<?php

declare(strict_types=1);

namespace Mitra\Command;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

final class FixtureLoadCommand extends Command
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct('mitra:fixtures:load');

        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setHelp('Loads fixtures from a path')
            ->addArgument('fixture-path', InputArgument::REQUIRED, 'Path to the fixture files');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $fixturePath = $input->getArgument('fixture-path');

        Assert::string($fixturePath);

        $output->write(sprintf('Loading fixtures from path `%s`... ', realpath($fixturePath)));

        $loader = new Loader();
        $loader->loadFromDirectory($fixturePath);

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());

        $output->writeln('Done!');

        return 0;
    }
}
