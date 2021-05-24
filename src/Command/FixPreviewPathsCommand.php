<?php

namespace App\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixPreviewPathsCommand extends Command
{
    private array $possibleTables = [
        'projects',
        'blogs'
    ];

    protected static $defaultName = 'app:fix-preview-paths';
    protected static $defaultDescription = 'update old preview pictures paths in database';

    private EntityManagerInterface $entityManager;

    private string $publicPath;

    private string $tableName;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);

        $this->publicPath = __DIR__.'/../../public';
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument(
                'table',
                InputArgument::REQUIRED,
                'Argument for Table ('.implode('/', $this->possibleTables).')'
            )
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only check if entries invalid')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->tableName = $input->getArgument('table');
        $dryRun = $input->getOption('dry-run') ? true : false;

        if (!$this->tableName) {
            $io->error(sprintf('Required argument table missing!'));
            return Command::FAILURE;
        }

        if ($this->tableName
            && !in_array($this->tableName, $this->possibleTables)
        ) {
            $io->error(sprintf('Table %s unknown!', $this->tableName));
            return Command::FAILURE;
        }

        $io->note(sprintf('Check for Table: %s', $this->tableName));

        if ($dryRun) {
            $io->note("and only check if missing entries exists!");
        }

        $entities = $this->entityManager->getRepository('App\Entity\\'.ucfirst($this->tableName))->findAll();
        $missingPreviewPaths = [];
        $fixedProjectIds = [];
        $stillMissing = [];
        foreach ($entities as $entity) {
            $absolutePreviewPicturePath = $this->publicPath.'/'.$entity->getPreviewPicture();
            if (!file_exists($absolutePreviewPicturePath)) {
                if ($this->fixMissingPreviewPath($entity)) {
                    $missingPreviewPaths[] = $entity;
                    if (!$dryRun) {
                        $this->entityManager->persist($entity);
                        $this->entityManager->flush();
                    }
                    $fixedProjectIds[] = $entity->getId();
                    continue;
                }
                $stillMissing[] = $entity->getId();
            }
        }
        $this->entityManager->clear();

        $io->success(count($fixedProjectIds).'/'.count($entities).' fixed - still missing: '.count($stillMissing).'!');

        return Command::SUCCESS;
    }

    private function fixMissingPreviewPath($entity)
    {
        $publicProjectPath = '/images/content/dynamisch/'.$this->tableName.'/'.$entity->getId().'/';

        $regex = '/\/images\/upload\/([0-9]{1,})\/'.$this->tableName.'\/(.*?)$/i';
        if (preg_match($regex, $entity->getPreviewPicture(), $matches)) {
            $publicPath = $publicProjectPath.$matches[2];
            $expectedPath = $this->publicPath.$publicPath;
            if (file_exists($expectedPath)
                && is_file($expectedPath)
            ) {
                $entity->setPreviewPicture($publicPath);
                $entity->setModified(new DateTime());
                return true;
            }
        }

        if (preg_match('/^[^\/]+\.[a-z0-9]+$/i', $entity->getPreviewPicture())) {
            $publicPath = $publicProjectPath.$entity->getPreviewPicture();
            $expectedPath = $this->publicPath.$publicPath;
            var_dump($expectedPath);
            if (file_exists($expectedPath)
                && is_file($expectedPath)
            ) {
                $entity->setPreviewPicture($publicPath);
                $entity->setModified(new DateTime());
                return true;
            }
        }

        $regex = '/^\/var\/www\/.*?\/public\/images\/content\/dynamisch\/'.
            $this->tableName.'\/[0-9]{1,}\/([^\/]+\.[0-9a-z]+)$/i';

        if (preg_match($regex, $entity->getPreviewPicture(), $matches)) {
            $publicPath = $publicProjectPath.$matches[1];
            $expectedPath = $this->publicPath.$publicPath;
            if (file_exists($expectedPath)
                && is_file($expectedPath)
            ) {
                $entity->setPreviewPicture($publicPath);
                $entity->setModified(new DateTime());
                return true;
            }
        }
        return false;
    }

    /**
     * Undocumented function
     *
     * @param EntityManagerInterface $entityManager
     * @return void
     *
     * @required
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
