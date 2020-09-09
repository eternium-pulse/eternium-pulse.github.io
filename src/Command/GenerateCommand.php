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

    private array $defaultContext = [];

    public function __construct(Twig $twig, Event ...$events)
    {
        $this->events = $events;
        $this->twig = $twig;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generates HTML content');
        $this->addOption('base-url', '', InputOption::VALUE_REQUIRED, 'Expand relative links using this URL', '/');
        $this->addOption('page-size', '', InputOption::VALUE_REQUIRED, 'Set LB page size', 100);
        $this->addOption('no-progress', '', InputOption::VALUE_NONE, 'Do not output load progress');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $url = $input->getOption('base-url');
        if (!($parts = @parse_url($url)) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new InvalidOptionException('The option "--base-url" requires valid URL without query or fragment parts.');
        }

        $pageSize = (int) $input->getOption('page-size');
        if (100 > $pageSize) {
            throw new InvalidOptionException('The option "--page-size" requires an integer at least 100.');
        }

        $this->defaultContext['site_name'] = 'Eternium Pulse';
        $this->defaultContext['base_url'] = rtrim($url, '/');
        $this->defaultContext['page_size'] = $pageSize;
        $this->defaultContext['events'] = $this->events;
        $this->defaultContext['stats'] = [];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $progressOutput = $input->getOption('no-progress') ? new NullOutput() : $output;

        $render = function (string $file, string $template, array $context = []) use ($output) {
            if (!str_ends_with($template, '.twig')) {
                $template .= '.twig';
            }

            Utils::dump(ETERNIUM_HTML_PATH.$file, $this->twig->render(
                $template,
                $context + $this->defaultContext,
            ));

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

        while (is_array($chain = yield)) {
            $event = $chain[0];
            $path = array_reverse($chain);
            $name = join('.', $path);
            $stats = &$this->defaultContext['stats'];
            $file = join('/', [...$path, 'index.html']);
            $sitemap[] = join('/', $path);

            if (!($event instanceof Leaderboard)) {
                $render($file, $event->getType(), compact('event', 'path'));

                $stats[$name] = ['count' => 0, 'max_clevel' => 0, 'max_tlevel' => 0];
                foreach ($event as $e) {
                    $stats[$name]['count'] += $stats["{$name}.{$e}"]['count'] ?? 0;
                    $stats[$name]['max_clevel'] = max($stats["{$name}.{$e}"]['max_clevel'] ?? 0, $stats[$name]['max_clevel']);
                    $stats[$name]['max_tlevel'] = max($stats["{$name}.{$e}"]['max_tlevel'] ?? 0, $stats[$name]['max_tlevel']);
                }

                continue;
            }

            $output->writeln($formatter->formatSection('LOAD', "loading {$name} entries..."));

            $reader = $event->read(ETERNIUM_DATA_PATH."{$name}.csv");
            $stats[$name] = ['max_clevel' => 0];
            $entries = [];

            try {
                $progressBar = new ProgressBar($progressOutput);
                $progressBar->setFormat($formatter->formatSection('LOAD', '%current% [%bar%] %elapsed%'));
                foreach ($progressBar->iterate($reader) as $entry) {
                    $max_clevel = max($entry['clevel'], $stats[$name]['max_clevel']);
                    $entries[] = $entry;
                }
            } finally {
                $progressBar->clear();
            }

            $output->writeln($formatter->formatSection('LOAD', "{$reader->getReturn()} entries loaded"));

            foreach (Utils::paginate(count($entries), $this->defaultContext['page_size']) as $page) {
                if ($page->first) {
                    $render($file, $event->getType(), compact('event', 'path', 'page', 'entries'));
                }
                $file = join('/', [...$path, "{$page->index}.html"]);
                $render($file, $event->getType(), compact('event', 'path', 'page', 'entries'));
            }

            $stats[$name] += [
                'max_tlevel' => $entries[0]['tlevel'] ?? 0,
                'count' => count($entries),
                'top_1' => $entries[0]['tlevel'] ?? 0,
                'top_10' => $entries[9]['tlevel'] ?? 0,
                'top_25' => $entries[24]['tlevel'] ?? 0,
                'top_50' => $entries[49]['tlevel'] ?? 0,
                'top_100' => $entries[99]['tlevel'] ?? 0,
                'top_250' => $entries[249]['tlevel'] ?? 0,
                'top_500' => $entries[499]['tlevel'] ?? 0,
                'top_1000' => $entries[999]['tlevel'] ?? 0,
            ];

            $entries = array_slice($entries, 0, 1000);
            Utils::dump(ETERNIUM_HTML_PATH.join('/', [...$path, 'data.json']), json_encode($entries));
            unset($entries);
        }

        return $sitemap;
    }
}
