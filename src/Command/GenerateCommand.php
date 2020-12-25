<?php

namespace Eternium\Command;

use Eternium\Event\EventInterface;
use Eternium\Event\Leaderboard;
use Eternium\Utils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment as Twig;
use Twig\TwigFunction;

class GenerateCommand extends Command
{
    protected static $defaultName = 'generate';

    private Twig $twig;

    /**
     * @var array<int, EventInterface>
     */
    private array $events;

    private string $origin = '';
    private string $basePath = '';

    private int $pageSize = 100;

    private bool $hideProgress = false;

    public function __construct(Twig $twig, EventInterface ...$events)
    {
        $this->events = $events;
        $this->twig = $twig;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generates HTML content');
        $this->addOption('base-url', '', InputOption::VALUE_REQUIRED, 'Expand relative links using this URL', 'http://localhost:8080');
        $this->addOption('page-size', '', InputOption::VALUE_REQUIRED, 'Set LB page size', $this->pageSize);
        $this->addOption('no-progress', '', InputOption::VALUE_NONE, 'Do not output load progress');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        [$this->origin, $this->basePath] = $this->parseUrl($input->getOption('base-url'));
        $this->basePath = rtrim($this->basePath, '/');
        $this->baseUri = $input->getOption('base-url');
        $output->writeln("Using <info>{$this->absUrl()}</info> as base URL", OutputInterface::VERBOSITY_VERY_VERBOSE);

        $this->pageSize = (int) $input->getOption('page-size');
        if (100 > $this->pageSize) {
            throw new InvalidOptionException('The option "--page-size" requires an integer at least 100.');
        }

        $this->hideProgress = $input->getOption('no-progress');

        $this->twig->addGlobal('eternium_url', 'https://www.eterniumgame.com/');
        $this->twig->addGlobal('events', $this->events);
        $this->twig->addGlobal('site', [
            'name' => $this->getApplication()->getName(),
            'theme' => '#343a40',
            'background' => '#ffffff',
        ]);

        $this->twig->addFunction(new TwigFunction(
            'event_path',
            fn (EventInterface $event, int $page = 1): string => $this->eventPath($event, $page)
        ));

        $this->twig->addFunction(new TwigFunction(
            'abs_path',
            fn (string $path = ''): string => $this->absPath($path)
        ));

        $this->twig->addFunction(new TwigFunction(
            'abs_url',
            fn (string $path = ''): string => $this->absUrl($path)
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $render = function (string $file, string $template, array $context = []) use ($output) {
            if (!str_ends_with($template, '.twig')) {
                $template .= '.twig';
            }

            Utils::dump(ETERNIUM_HTML_PATH.$file, $this->twig->render($template, $context));

            $output->writeln(
                $this->getHelper('formatter')->formatSection('HTML', "<href={$this->baseUri}/{$file}>{$file}</> generated using {$template}", 'comment'),
                OutputInterface::VERBOSITY_VERBOSE,
            );
        };

        $generator = $this->createGenerator($render, $output);
        foreach ($this->events as $event) {
            $event->walk($generator);
        }
        $generator->send(null);

        $render('index.html', 'index');
        $render('403.html', 'error', ['code' => 403, 'message' => 'Forbidden']);
        $render('404.html', 'error', ['code' => 404, 'message' => 'Not found']);
        $render('manifest.webmanifest', 'manifest');
        $render('sitemap.xml', 'sitemap', ['urls' => $generator->getReturn()]);
        $render('robots.txt', 'robots');

        return self::SUCCESS;
    }

    private function createGenerator(callable $render, OutputInterface $output): \Generator
    {
        $sitemap = [];
        $formatter = $this->getHelper('formatter');
        $progressBar = new ProgressBar($this->hideProgress ? new NullOutput() : $output);
        $progressBar->setFormat($formatter->formatSection('LOAD', '%message% %current% %elapsed%'));

        while (null !== ($event = yield )) {
            $path = $event->getPath('/');
            $sitemap[] = $this->eventPath($event);
            $template = strtolower($event->type);

            if (!($event instanceof Leaderboard)) {
                $render("{$path}/index.html", $template, compact('event'));

                continue;
            }

            $reader = $event->read(ETERNIUM_DATA_PATH."{$path}.csv");

            try {
                $progressBar->setMessage("loading {$path}");
                $progressBar->display();
                $entries = iterator_to_array($progressBar->iterate($reader), false);
            } finally {
                $progressBar->clear();
            }

            $output->writeln($formatter->formatSection('LOAD', "{$path} loaded ({$reader->getReturn()} entries)"));

            $page_size = $this->pageSize;
            foreach (Utils::paginate(count($entries), $page_size) as $page) {
                if ($page->first) {
                    $render("{$path}/index.html", $template, compact('event', 'page', 'page_size', 'entries'));
                }
                $render("{$path}/{$page->index}.html", $template, compact('event', 'page', 'page_size', 'entries'));
            }

            unset($entries);
        }

        return $sitemap;
    }

    private function eventPath(EventInterface $event, int $page = 0): string
    {
        assert(0 <= $page);

        $path = $event->getPath('/').'/';
        if (1 < $page) {
            $path .= "{$page}.html";
        }

        return $path;
    }

    private function absPath(string $path = ''): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return "{$this->basePath}/{$path}";
    }

    private function absUrl(string $path = ''): string
    {
        return $this->origin.$this->absPath($path);
    }

    private function parseUrl(string $url): array
    {
        $parts = parse_url($url) ?: [];
        $origin = '';
        if (isset($parts['host'])) {
            $origin = $parts['host'];
            if (isset($parts['port'])) {
                $origin .= ':'.$parts['port'];
            }
            if (isset($parts['user'])) {
                $origin = '@'.$origin;
                if (isset($parts['pass'])) {
                    $origin = ':'.$parts['pass'].$origin;
                }
                $origin = $parts['user'].$origin;
            }
            $origin = '//'.$origin;
        }
        if (isset($parts['scheme'])) {
            $origin = $parts['scheme'].':'.$origin;
        }

        return [$origin, $parts['path'] ?? ''];
    }
}
