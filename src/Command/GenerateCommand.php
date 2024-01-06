<?php

namespace Eternium\Command;

use Eternium\Config;
use Eternium\Event\Event;
use Eternium\Event\Leaderboard;
use Eternium\Sitemap\Sitemap;
use Eternium\Utils;
use Eternium\Utils\Minifier;
use League\Uri\Contracts\UriInterface;
use League\Uri\Uri;
use League\Uri\UriResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\TwigFunction;

class GenerateCommand extends Command
{
    protected static $defaultName = 'generate';

    /**
     * @var Leaderboard[]
     */
    private array $leaderboards = [];

    private Uri $baseUrl;

    private int $pageSize = 100;

    private int $pageLimit = 0;

    private bool $ignoreData = false;

    private bool $hideProgress = false;

    public function __construct(private Config $config)
    {
        $this->baseUrl = Uri::createFromString(getenv('CI_PAGES_URL') ?: 'http://localhost:8080');
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generates HTML content');
        $this->addOption('base-url', '', InputOption::VALUE_REQUIRED, 'Expand relative links using this URL', (string) $this->baseUrl);
        $this->addOption('page-size', '', InputOption::VALUE_REQUIRED, 'Set LB page size', $this->pageSize);
        $this->addOption('page-limit', '', InputOption::VALUE_REQUIRED, 'Limit number of LB pages', $this->pageLimit);
        $this->addOption('no-data', '', InputOption::VALUE_NONE, 'Do not load data files');
        $this->addOption('no-progress', '', InputOption::VALUE_NONE, 'Do not output load progress');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        try {
            $this->baseUrl = Uri::createFromString($input->getOption('base-url'));
            if (null === $this->baseUrl->getAuthority()) {
                throw new \UnexpectedValueException('Unexpected authority.');
            }
            if (!\in_array($this->baseUrl->getScheme(), ['http', 'https'])) {
                throw new \UnexpectedValueException('Scheme requries http or https.');
            }
        } catch (\Throwable $ex) {
            throw new InvalidOptionException('The option "--base-url" requires a valid URL.', previous: $ex);
        }
        $output->writeln("Using <info>{$this->baseUrl}</info> as base URL", OutputInterface::VERBOSITY_VERY_VERBOSE);

        $this->pageSize = (int) $input->getOption('page-size');
        if (100 > $this->pageSize) {
            throw new InvalidOptionException('The option "--page-size" requires an integer at least 100.');
        }

        $this->pageLimit = (int) $input->getOption('page-limit');
        if (0 >= $this->pageLimit) {
            $this->pageLimit = PHP_INT_MAX;
        }

        $this->ignoreData = $input->getOption('no-data');
        $this->hideProgress = $input->getOption('no-progress');

        $this->config->twig->addGlobal('site', [
            'name' => $this->getApplication()->getName(),
            'theme' => '#343a40',
            'background' => '#ffffff',
        ]);

        $latest_events = [];
        foreach ($this->config->events as $event) {
            $latest_events[$event->type] = $event;
        }
        $this->config->twig->addGlobal('latest_events', $latest_events);

        $this->config->twig->addFunction(new TwigFunction(
            'event_path',
            fn (Event $event, int $page = 1): string => $this->eventPath($event, $page),
        ));

        $this->config->twig->addFunction(new TwigFunction(
            'abs_path',
            fn (string $path = ''): string => $this->absUrl($path)->getPath(),
        ));

        $this->config->twig->addFunction(new TwigFunction(
            'abs_url',
            fn (string $path = ''): UriInterface => $this->absUrl($path),
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->minify([
            "{$this->config->htmlPath}/js/eternium.min.js" => [
                "{$this->config->htmlPath}/js/eternium.js",
            ],
            "{$this->config->htmlPath}/js/counters.min.js" => [
                "{$this->config->htmlPath}/js/counters.js",
            ],
            "{$this->config->htmlPath}/js/index.min.js" => [
                "{$this->config->htmlPath}/js/counters.js",
                "{$this->config->htmlPath}/js/index.js",
            ],
        ]);

        $render = function (string $file, string $template, array $context = []) use ($output) {
            if (!str_ends_with($template, '.twig')) {
                $template .= '.twig';
            }

            Utils::dump("{$this->config->htmlPath}/{$file}", $this->config->twig->render($template, $context));

            $href = UriResolver::resolve(Uri::createFromComponents(['path' => $file]), $this->baseUrl);
            $output->writeln(
                $this->getHelper('formatter')->formatSection('HTML', "<href={$href}>{$file}</> generated using {$template}", 'comment'),
                OutputInterface::VERBOSITY_VERBOSE,
            );
        };

        $generator = $this->createGenerator($render, $output);
        foreach ($this->config->events as $event) {
            $event->walk($generator);
        }
        $generator->send(null);

        $render('index.html', 'index', [
            'status' => $this->config->gameStatus,
            'gameEvents' => $this->config->gameEvents,
            'leaderboards' => $this->leaderboards,
        ]);
        $render('403.html', 'error', ['code' => 403, 'message' => 'Forbidden']);
        $render('404.html', 'error', ['code' => 404, 'message' => 'Not found']);
        $render('manifest.webmanifest', 'manifest');
        $render('sitemap.xml', 'sitemap', ['urlset' => $generator->getReturn()]);
        $render('robots.txt', 'robots');

        return self::SUCCESS;
    }

    private function createGenerator(callable $render, OutputInterface $output): \Generator
    {
        $sitemap = new Sitemap();
        $formatter = $this->getHelper('formatter');
        $progressBar = new ProgressBar($this->hideProgress ? new NullOutput() : $output);
        $progressBar->setFormat($formatter->formatSection('LOAD', '%message% %current% %elapsed%'));

        while (null !== ($event = yield)) {
            $path = join('/', $event->getPath());
            $template = $event->type;

            if (!$event instanceof Leaderboard) {
                $render("{$path}/index.html", $template, compact('event'));

                continue;
            }

            $file = new \SplFileInfo("{$this->config->dataPath}/{$path}.csv");
            $mtime = \DateTimeImmutable::createFromFormat(
                'U',
                ((int) `git log -1 --format=%at -- "{$file->getRealPath()}"`) ?: $file->getMTime(),
            );
            $sitemap->add($this->absUrl($this->eventPath($event)), lastmod: $mtime);

            $this->leaderboards[$event->id] = $event;
            $reader = $this->ignoreData ? Utils::createNullReader() : $event->read($file);

            try {
                $progressBar->setMessage("loading {$path}");
                $progressBar->display();
                $entries = iterator_to_array($progressBar->iterate($reader), false);
            } finally {
                $progressBar->clear();
            }

            $output->writeln($formatter->formatSection('LOAD', "{$path} loaded ({$reader->getReturn()} entries)"));

            foreach (Utils::paginate(count($entries), $this->pageSize, $this->pageLimit) as $page) {
                $context = [
                    'event' => $event,
                    'page' => $page,
                    'page_size' => $this->pageSize,
                    'entries' => $entries,
                    'modified' => $mtime,
                ];
                if ($page->first) {
                    $render("{$path}/index.html", $template, $context);
                }
                $render("{$path}/{$page->index}.html", $template, $context);
            }

            unset($context, $entries);
        }

        return $sitemap;
    }

    private function eventPath(Event $event, int $page = 0): string
    {
        assert(0 <= $page);

        $path = join('/', $event->getPath()).'/';
        if (1 < $page) {
            $path .= "{$page}.html";
        }

        return $path;
    }

    private function minify(array $assets): void
    {
        $minifier = new Minifier();

        foreach ($assets as $dst => $src) {
            $code = '';
            $type = (string) pathinfo($dst, \PATHINFO_EXTENSION);
            foreach ($src as $file) {
                $code .= Utils::read($file);
            }
            Utils::dump($dst, $minifier->tryMinify($type, $code));
        }
    }

    private function absUrl(string $path = ''): UriInterface
    {
        return UriResolver::resolve(
            Uri::createFromComponents(['path' => $path]),
            $this->baseUrl,
        );
    }
}
