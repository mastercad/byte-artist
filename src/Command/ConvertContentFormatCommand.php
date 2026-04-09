<?php

namespace App\Command;

use App\Service\Filter\Ubb\Replace;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:convert-content-format',
    description: 'Convert UBB/BBCode content to HTML for all blog or project entries'
)]
class ConvertContentFormatCommand extends Command
{
    private const POSSIBLE_TABLES = [
        'blogs',
        'projects',
    ];

    /** UBB opening-tag pattern – presence indicates the content needs conversion */
    private const UBB_DETECTION_PATTERN = '/\[[A-Z][A-Z0-9]*[=:]?[^\]]*\]/i';

    private array $imagePathMap = [
        'blogs'    => '/images/content/dynamisch/blogs/%d/',
        'projects' => '/images/content/dynamisch/projects/%d/',
    ];

    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'table',
                InputArgument::REQUIRED,
                'Table to process (' . implode('/', self::POSSIBLE_TABLES) . ')'
            )
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be changed without writing to DB')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Convert all entries, even those without detected UBB tags')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tableName = $input->getArgument('table');
        $dryRun    = (bool) $input->getOption('dry-run');
        $force     = (bool) $input->getOption('force');

        if (!in_array($tableName, self::POSSIBLE_TABLES, true)) {
            $io->error(sprintf('Unknown table "%s". Allowed: %s', $tableName, implode(', ', self::POSSIBLE_TABLES)));

            return Command::FAILURE;
        }

        $entityClass = 'App\\Entity\\' . ucfirst($tableName);
        $entities    = $this->entityManager->getRepository($entityClass)->findAll();

        $io->title(sprintf('Convert UBB → HTML for table "%s" (%d entries)', $tableName, count($entities)));

        if ($dryRun) {
            $io->note('DRY-RUN – no changes will be written to the database.');
        }

        $converted = 0;
        $skipped   = 0;

        foreach ($entities as $entity) {
            $id      = $entity->getId();
            $content = $this->getContent($entity, $tableName);

            if (null === $content || '' === $content) {
                ++$skipped;
                continue;
            }

            $hasUbbTags = (bool) preg_match(self::UBB_DETECTION_PATTERN, $content);

            if (!$hasUbbTags && !$force) {
                $io->writeln(sprintf('  [skip] id=%d – no UBB tags detected', $id), OutputInterface::VERBOSITY_VERBOSE);
                ++$skipped;
                continue;
            }

            $imagePath = sprintf($this->imagePathMap[$tableName], $id);
            $replacer  = new Replace(true);
            $replacer->setBilderPfad($imagePath);

            $html = $replacer->filter($content);

            if ($html === $content) {
                $io->writeln(sprintf('  [noop] id=%d – content unchanged after conversion', $id), OutputInterface::VERBOSITY_VERBOSE);
                ++$skipped;
                continue;
            }

            $io->writeln(sprintf('  [convert] id=%d', $id));

            if (!$dryRun) {
                $this->setContent($entity, $tableName, $html);
                $entity->setModified(new \DateTime());
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
            }

            ++$converted;
        }

        $this->entityManager->clear();

        $io->success(sprintf('%d converted, %d skipped (total %d).', $converted, $skipped, count($entities)));

        return Command::SUCCESS;
    }

    private function getContent(object $entity, string $tableName): ?string
    {
        return match ($tableName) {
            'blogs'    => $entity->getContent(),
            'projects' => $entity->getDescription(),
            default    => null,
        };
    }

    private function setContent(object $entity, string $tableName, string $html): void
    {
        match ($tableName) {
            'blogs'    => $entity->setContent($html),
            'projects' => $entity->setDescription($html),
            default    => null,
        };
    }
}
