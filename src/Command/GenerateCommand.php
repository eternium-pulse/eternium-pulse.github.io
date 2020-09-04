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
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment as Twig;

class GenerateCommand extends Command
{
    protected static $defaultName = 'generate';

    private string $publicDir;

    private Twig $twig;

    /**
     * @var array<int, Event>
     */
    private array $events;

    public function __construct(Twig $twig, Event ...$events)
    {
        $this->events = $events;
        $this->publicDir = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'public';
        $this->twig = $twig;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generates HTML content');
        $this->addOption('data-dir', 'd', InputOption::VALUE_REQUIRED, 'Load data from this directory', 'data');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $dir = $input->getOption('data-dir');
        if (!is_dir($dir)) {
            throw new InvalidOptionException('The option "--data-dir" requires an existing directory.');
        }
        $input->setOption('data-dir', realpath($dir));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Generate events structure
        $params = [];
        foreach ($this->events as $event) {
            $params[$event->getType()][$event->getName()] = $event->apply(static fn () => []);
        }

        $path = $input->getOption('data-dir');

        $generator = function (Leaderboard $leaderboard, string ...$names) use ($params, $path, $output): array {
            $formatter = $this->getHelper('formatter');
            $name = join('.', $names);

            $output->writeln($formatter->formatSection('LOAD', "loading {$name} entries..."));

            $reader = Utils::createCsvReader($path.DIRECTORY_SEPARATOR."{$name}.csv");
            $maxChampionLevel = 0;
            $entries = [];

            try {
                $progressBar = new ProgressBar($output);
                foreach ($progressBar->iterate($reader) as $entry) {
                    $entry = [
                        'name' => $entry[0],
                        'title' => $entry[1],
                        'champion_level' => (int) $entry[2],
                        'score' => (int) $entry[3],
                        'deaths' => (int) $entry[4],
                    ];
                    $maxChampionLevel = max($entry['champion_level'], $maxChampionLevel);
                    $entries[] = $entry;
                }
            } finally {
                $progressBar->clear();
            }

            $output->writeln($formatter->formatSection('LOAD', "{$reader->getReturn()} entries loaded"));

            $params['entries'] = $entries;
            $this->generateLeaderboard($names, $params);

            return [
                'id' => $leaderboard->getId(),
                'class' => $leaderboard->getName(),
                'count' => count($entries),
                'max_champion_level' => $maxChampionLevel,
                'top_1' => $entries[0]['score'] ?? 0,
                'top_10' => $entries[9]['score'] ?? 0,
                'top_25' => $entries[24]['score'] ?? 0,
                'top_50' => $entries[49]['score'] ?? 0,
                'top_100' => $entries[99]['score'] ?? 0,
                'top_250' => $entries[249]['score'] ?? 0,
                'top_500' => $entries[499]['score'] ?? 0,
                'top_1000' => $entries[999]['score'] ?? 0,
            ];
        };

        // Generate leaderboards and calc stats
        foreach ($this->events as $event) {
            $params[$event->getType()][$event->getName()] = $event->apply($generator);
        }

        $this->generate($params);

        return self::SUCCESS;
    }

    private function generate(array $params): void
    {
        $this->renderTo('index.html', 'index', $params);

        foreach ($params as $type => $events) {
            $params['event'] = compact('type');
            $this->renderTo("{$type}/index.html", "{$type}/index", $params);
            foreach ($events as $id => $event) {
                $params['event'] = compact('type', 'id');
                $this->renderTo("{$type}/{$id}/index.html", "{$type}/event", $params);
                if ('anb' === $type) {
                    foreach (array_keys($event) as $league) {
                        $params['event'] = compact('type', 'id', 'league');
                        $this->renderTo("{$type}/{$id}/{$league}/index.html", "{$type}/league", $params);
                    }
                }
            }
        }
    }

    private function generateLeaderboard(array $names, array $params): void
    {
        $keys = ['type', 'id'];
        if ('anb' === $names[0]) {
            $keys[] = 'league';
        }
        $keys[] = 'class';
        $params['event'] = array_combine($keys, $names);

        $path = join('/', $names);
        $this->renderTo("{$path}/index.html", "{$names[0]}/leaderboard", $params);
    }

    private function renderTo(string $file, string $template, array $context = []): void
    {
        $context['strings'] = [
            'season' => 'Season',
            'anb' => 'ANB',
            'bronze' => 'Bronze',
            'silver' => 'Silver',
            'gold' => 'Gold',
            'mage' => 'Mage',
            'warrior' => 'Warrior',
            'bountyhunter' => 'Bounty Hunter',
        ];

        Utils::dump("{$this->publicDir}/{$file}", $this->twig->render("{$template}.html", $context));
    }
}
