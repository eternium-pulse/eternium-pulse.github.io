<?php

namespace Eternium\Command;

use Eternium\Event\Event;
use Eternium\Event\Leaderboard;
use Eternium\Utils;
use Eternium\Utils\Minifier;
use League\Uri\Uri;
use League\Uri\UriResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment as Twig;
use Twig\TwigFunction;

class GenerateCommand extends Command
{
    protected static $defaultName = 'generate';

    private Uri $baseUrl;

    private int $pageSize = 100;

    private int $pageLimit = 0;

    private bool $ignoreData = false;

    private bool $hideProgress = false;

    private array $turboItems = [];

    private array $news = [];

    private array $gameEvents = [];

    public function __construct(
        private Twig $twig,
        // @var array<int, Event>
        private array $events,
    ) {
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

        $this->twig->addGlobal('eternium_url', 'https://www.eterniumgame.com/');
        $this->twig->addGlobal('events', $this->events);
        $this->twig->addGlobal('site', [
            'name' => $this->getApplication()->getName(),
            'theme' => '#343a40',
            'background' => '#ffffff',
        ]);

        $latest_events = [];
        foreach ($this->events as $event) {
            $latest_events[$event->type] = $event;
        }
        $this->twig->addGlobal('latest_events', $latest_events);

        $this->twig->addFunction(new TwigFunction(
            'event_path',
            fn (Event $event, int $page = 1): string => $this->eventPath($event, $page),
        ));

        $this->twig->addFunction(new TwigFunction(
            'abs_path',
            fn (string $path = ''): string => UriResolver::resolve(
                Uri::createFromComponents(['path' => $path]),
                $this->baseUrl,
            )->getPath(),
        ));

        $this->twig->addFunction(new TwigFunction(
            'abs_url',
            fn (string $path = ''): string => (string) UriResolver::resolve(
                Uri::createFromComponents(['path' => $path]),
                $this->baseUrl,
            ),
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = HttpClient::createForBaseUri('https://eternium.alex-tsarkov.workers.dev/api/');
        $this->fetchGameEvents($client);

        $this->minify([
            ETERNIUM_HTML_PATH.'/js/eternium.min.js' => [
                ETERNIUM_HTML_PATH.'/js/eternium.js',
            ],
            ETERNIUM_HTML_PATH.'/js/counters.min.js' => [
                ETERNIUM_HTML_PATH.'/js/counters.js',
            ],
            ETERNIUM_HTML_PATH.'/js/index.min.js' => [
                ETERNIUM_HTML_PATH.'/js/counters.js',
                ETERNIUM_HTML_PATH.'/js/eternium.js',
                ETERNIUM_HTML_PATH.'/js/index.js',
            ],
        ]);

        $render = function (string $file, string $template, array $context = []) use ($output) {
            if (!str_ends_with($template, '.twig')) {
                $template .= '.twig';
            }

            Utils::dump(ETERNIUM_HTML_PATH.$file, $this->twig->render($template, $context));

            $href = UriResolver::resolve(Uri::createFromComponents(['path' => $file]), $this->baseUrl);
            $output->writeln(
                $this->getHelper('formatter')->formatSection('HTML', "<href={$href}>{$file}</> generated using {$template}", 'comment'),
                OutputInterface::VERBOSITY_VERBOSE,
            );
        };

        $generator = $this->createGenerator($render, $output);
        foreach ($this->events as $event) {
            $event->walk($generator);
        }
        $generator->send(null);

        $render('index.html', 'index', ['news' => $this->news, 'gameEvents' => $this->gameEvents]);
        $render('403.html', 'error', ['code' => 403, 'message' => 'Forbidden']);
        $render('404.html', 'error', ['code' => 404, 'message' => 'Not found']);
        $render('manifest.webmanifest', 'manifest');
        $render('sitemap.xml', 'sitemap', ['urls' => $generator->getReturn()]);
        $render('turbo.rss', 'turbo', ['items' => $this->turboItems]);
        $render('robots.txt', 'robots');

        return self::SUCCESS;
    }

    private function createGenerator(callable $render, OutputInterface $output): \Generator
    {
        $sitemap = [];
        $formatter = $this->getHelper('formatter');
        $progressBar = new ProgressBar($this->hideProgress ? new NullOutput() : $output);
        $progressBar->setFormat($formatter->formatSection('LOAD', '%message% %current% %elapsed%'));

        while (null !== ($event = yield)) {
            $path = join('/', $event->getPath());
            $sitemap[] = $this->eventPath($event);
            $template = $event->type;

            if (!$event instanceof Leaderboard) {
                $render("{$path}/index.html", $template, compact('event'));

                continue;
            }

            $reader = $this->ignoreData ? Utils::createNullReader() : $event->read(ETERNIUM_DATA_PATH."{$path}.csv");

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
                ];
                if ($page->first) {
                    $render("{$path}/index.html", $template, $context);
                }
                $render("{$path}/{$page->index}.html", $template, $context);
            }

            $this->turboItems[] = $this->twig->render("turbo/{$event->parent->type}.twig", [
                'event' => $event,
                'entries' => array_slice($entries, 0, $this->pageSize),
            ]);

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

    private function fetchGameEvents(HttpClientInterface $client): void
    {
        $this->gameEvents = $client->request('GET', 'v3/getGameEvents')->toArray()['events'];
        foreach ($this->gameEvents as &$event) {
            $event['start_date'] = \DateTimeImmutable::createFromFormat('U', $event['start_date'] / 1000, new \DateTimeZone('UTC'));
            $event['end_date'] = \DateTimeImmutable::createFromFormat('U', $event['end_date'] / 1000, new \DateTimeZone('UTC'));
        }
    }

    private function fetchNews(HttpClientInterface $client): void
    {
        $this->news = $client->request('GET', 'v2/getNews')->toArray()['news'];
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
}
