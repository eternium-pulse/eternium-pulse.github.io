<?php

namespace Eternium\Command;

use Eternium\Event\Event;
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
     * @var array<int, Event>
     */
    private array $events;

    private int $pageSize;

    public function __construct(Twig $twig, Event ...$events)
    {
        $this->events = $events;
        $this->twig = $twig;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generates HTML content');
        $this->addOption('base-url', '', InputOption::VALUE_REQUIRED, 'Expand relative links using this URL', 'http://localhost:8080');
        $this->addOption('page-size', '', InputOption::VALUE_REQUIRED, 'Set LB page size', 100);
        $this->addOption('no-progress', '', InputOption::VALUE_NONE, 'Do not output load progress');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $url = $input->getOption('base-url');
        if (!($parts = @parse_url($url)) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new InvalidOptionException('The option "--base-url" requires valid URL without query or fragment parts.');
        }
        $this->twig->addGlobal('base_url', $url);

        $this->pageSize = (int) $input->getOption('page-size');
        if (100 > $this->pageSize) {
            throw new InvalidOptionException('The option "--page-size" requires an integer at least 100.');
        }

        $this->twig->addGlobal('events', $this->events);
        $this->twig->addGlobal('site_name', $this->getApplication()->getName());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $progressOutput = $input->getOption('no-progress') ? new NullOutput() : $output;

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

        $generator = $this->createGenerator($render, $output, $progressOutput);
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

    private function createGenerator(callable $render, OutputInterface $output, OutputInterface $progressOutput): \Generator
    {
        $sitemap = [];
        $formatter = $this->getHelper('formatter');

        while (null !== ($event = yield)) {
            $name = $event->getPath('.');
            $path = $event->getPath('/');
            $sitemap[] = $path;

            if (!($event instanceof Leaderboard)) {
                $render("{$path}/index.html", $event->type, compact('event'));

                continue;
            }

            $output->writeln($formatter->formatSection('LOAD', "loading {$name} entries..."));

            $reader = $event->read(ETERNIUM_DATA_PATH."{$name}.csv");

            try {
                $progressBar = new ProgressBar($progressOutput);
                $progressBar->setFormat($formatter->formatSection('LOAD', '%current% [%bar%] %elapsed%'));
                $entries = iterator_to_array($progressBar->iterate($reader), false);
            } finally {
                $progressBar->clear();
            }

            $output->writeln($formatter->formatSection('LOAD', "{$reader->getReturn()} entries loaded"));

            $page_size = $this->pageSize;
            foreach (Utils::paginate(count($entries), $page_size) as $page) {
                if ($page->first) {
                    $render("{$path}/index.html", $event->type, compact('event', 'page', 'page_size', 'entries'));
                }
                $render("{$path}/{$page->index}.html", $event->type, compact('event', 'page', 'page_size', 'entries'));
            }

            $entries = array_slice($entries, 0, $page_size);
            Utils::dump(ETERNIUM_HTML_PATH.$path.'/data.json', json_encode($entries));
            unset($entries);
        }

        return $sitemap;
    }
}
