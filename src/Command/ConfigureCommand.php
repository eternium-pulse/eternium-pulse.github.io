<?php

namespace Eternium\Command;

use Eternium\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('configure', 'Configure current game events')]
class ConfigureCommand extends Command
{
    public function __construct(private readonly Config $config)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->config->gameEvents as $event) {
            $output->writeln($this->formatEvent($event));
        }

        return self::SUCCESS;
    }

    private function formatEvent(array $event): array
    {
        if (!empty($event['isSeason'])) {
            return $this->formatSeason($event);
        }
        if (\str_contains((string) $event['id'], 'anb_')) {
            return $this->formatAnb($event);
        }

        return [];
    }

    private function formatSeason(array $event): array
    {
        return [
            'Season::create(',
            ...$this->indent(
                \sprintf('%d,', 0),
                \sprintf("'%s',", \ucwords(\strtr($event['id'], '_#', '  '))),
                \sprintf("mages: '%s',", $event['leaderboards']['trial_mage']),
                \sprintf("warriors: '%s',", $event['leaderboards']['trial_warrior']),
                \sprintf("bounty_hunters: '%s',", $event['leaderboards']['trial_bountyhunter']),
            ),
            '),',
        ];
    }

    private function formatAnb(array $event): array
    {
        [, $league, $index] = \explode('_', (string) $event['id']);

        return [
            'Anb::create(',
            ...$this->indent(...[
                \sprintf('%d,', $index),
                \sprintf('League::create%s(brackets: [', \ucfirst($league)),
                ...$this->indent(...[
                    ...$this->formatBracket($event['leaderboards'], 'contender'),
                    ...$this->formatBracket($event['leaderboards'], 'veteran'),
                    ...$this->formatBracket($event['leaderboards'], 'master'),
                ]),
                ']),',
            ]),
            '),',
        ];
    }

    private function formatBracket(array $leaderboards, string $name): array
    {
        return [
            \sprintf("'%s' => [", $name),
            ...$this->indent(
                \sprintf("'mages' => '%s',", $leaderboards[$name]['trial_mage']),
                \sprintf("'warriors' => '%s',", $leaderboards[$name]['trial_warrior']),
                \sprintf("'bounty_hunters' => '%s',", $leaderboards[$name]['trial_bountyhunter']),
            ),
            '],',
        ];
    }

    private function indent(string ...$lines): array
    {
        foreach ($lines as &$line) {
            $line = '    '.$line;
        }

        return $lines;
    }
}
