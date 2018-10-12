<?php

namespace Yokai\SecurityTokenBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yokai\SecurityTokenBundle\Archive\ArchivistInterface;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class ArchiveTokenCommand extends ContainerAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('yokai:security-token:archive')
            ->addOption('purpose', null, InputOption::VALUE_OPTIONAL, 'Filter tokens to archive on purpose.')
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $purpose = $input->getOption('purpose');

        /** @var $archivist ArchivistInterface */
        $archivist = $this->getContainer()->get('yokai_security_token.archivist');

        $count = $archivist->archive($purpose);

        $output->writeln(
            sprintf('<info>Successfully archived <comment>%d</comment> security token(s).</info>', $count)
        );
    }
}
