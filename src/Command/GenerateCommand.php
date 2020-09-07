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
        $this->addOption('no-progress', '', InputOption::VALUE_NONE, 'Do not output load progress');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $url = $input->getOption('base-url');
        if (!($parts = @parse_url($url)) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new InvalidOptionException('The option "--base-url" requires valid URL without query or fragment parts.');
        }
        if (!str_ends_with($url, '/')) {
            $url .= '/';
        }

        $this->defaultContext['base_url'] = $url;
        $this->defaultContext['events'] = $this->events;
        $this->defaultContext['stats'] = [];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $progressOutput = $input->getOption('no-progress') ? new NullOutput() : $output;

        $render = function (string $file, string $template, array $context = []) use ($output) {
            Utils::dump(ETERNIUM_HTML_PATH.$file, $this->twig->render(
                "{$template}.html",
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
        $render('403.html', '403');
        $render('404.html', '404');

        return self::SUCCESS;
    }

    private function createGenerator(callable $render, OutputInterface $output, OutputInterface $progressOutput): \Generator
    {
        $formatter = $this->getHelper('formatter');

        while (is_array($chain = yield)) {
            $event = $chain[0];
            $path = array_reverse($chain);
            $name = join('.', $path);
            $stats = &$this->defaultContext['stats'];

            if (!($event instanceof Leaderboard)) {
                $file = join(DIRECTORY_SEPARATOR, [...$path, 'index.html']);
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

            $file = join(DIRECTORY_SEPARATOR, [...$path, 'index.html']);
            $render($file, $event->getType(), compact('event', 'path', 'entries'));

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
            unset($entries);
        }
    }
}
