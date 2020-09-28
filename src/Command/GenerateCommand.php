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

class GenerateCommand extends Command
{
    protected static $defaultName = 'generate';

    private Twig $twig;

    /**
     * @var array<int, EventInterface>
     */
    private array $events;

    private string $baseUri = 'http://localhost:8080';

    private int $pageSize = 100;

    private bool $pingSitemap = false;

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
        $this->addOption('base-url', '', InputOption::VALUE_REQUIRED, 'Expand relative links using this URL', $this->baseUri);
        $this->addOption('page-size', '', InputOption::VALUE_REQUIRED, 'Set LB page size', $this->pageSize);
        $this->addOption('no-progress', '', InputOption::VALUE_NONE, 'Do not output load progress');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->baseUri = $input->getOption('base-url');
        if (!($parts = @parse_url($this->baseUri)) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new InvalidOptionException('The option "--base-url" requires valid URL without query or fragment parts.');
        }
        $this->baseUri = rtrim($this->baseUri, '/');

        $this->pageSize = (int) $input->getOption('page-size');
        if (100 > $this->pageSize) {
            throw new InvalidOptionException('The option "--page-size" requires an integer at least 100.');
        }

        $this->hideProgress = $input->getOption('no-progress');

        $this->twig->addGlobal('base_url', $this->baseUri);
        $this->twig->addGlobal('eternium_url', 'https://www.eterniumgame.com/');
        $this->twig->addGlobal('events', $this->events);
        $this->twig->addGlobal('site_name', $this->getApplication()->getName());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $render = function (string $file, string $template, array $context = []) use ($output) {
            if (!str_ends_with($template, '.twig')) {
                $template .= '.twig';
            }

            Utils::dump(ETERNIUM_HTML_PATH.$file, $this->twig->render($template, $context));

            $output->writeln(
                $this->getHelper('formatter')->formatSection('HTML', "{$file} generated using {$template}", 'comment'),
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
        $render('sitemap.txt', 'sitemap', ['urls' => $generator->getReturn()]);
        $render('robots.txt', 'robots');

        return self::SUCCESS;
    }

    private function createGenerator(callable $render, OutputInterface $output): \Generator
    {
        $sitemap = [];
        $formatter = $this->getHelper('formatter');
        $progressBar = new ProgressBar($this->hideProgress ? new NullOutput() : $output);
        $progressBar->setFormat($formatter->formatSection('LOAD', '%current% [%bar%] %elapsed%'));

        while (null !== ($event = yield)) {
            $name = $event->getPath('.');
            $path = $event->getPath('/');
            $sitemap[] = $path;
            $template = strtolower($event->type);

            if (!($event instanceof Leaderboard)) {
                $render("{$path}/index.html", $template, compact('event'));

                continue;
            }

            $output->writeln($formatter->formatSection('LOAD', "loading {$name} entries..."));

            $reader = $event->read(ETERNIUM_DATA_PATH."{$name}.csv");

            try {
                $progressBar->display();
                $entries = iterator_to_array($progressBar->iterate($reader), false);
            } finally {
                $progressBar->clear();
            }

            $output->writeln($formatter->formatSection('LOAD', "{$reader->getReturn()} entries loaded"));

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
}
