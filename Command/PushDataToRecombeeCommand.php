<?php

namespace MauticPlugin\MauticRecombeeBundle\Command;

use Mautic\CoreBundle\Translation\Translator;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticRecombeeBundle\Api\Service\ApiCommands;
use MauticPlugin\MauticRecombeeBundle\Api\Service\ApiUserItemsInteractions;
use MauticPlugin\MauticRecombeeBundle\Helper\RecombeeHelper;
use Recombee\RecommApi\Requests as Reqs;
use Recombee\RecommApi\Exceptions as Ex;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PushDataToRecombeeCommand extends ContainerAwareCommand
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var array
     */
    private $types = ['contacts', 'items'];

    /**
     * @var array
     */
    //private $actions = ['views', 'carts', 'purchases', 'bookmarks', 'ratings'];
    private $actions = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mautic:integration:recombee:import')
            ->setDescription('Import data to Recombee')
            ->addOption(
                '--type',
                '-t',
                InputOption::VALUE_REQUIRED,
                'Type options: '.implode(', ', $this->getTypes()),
                null
            )->addOption(
                '--file',
                '-f',
                InputOption::VALUE_OPTIONAL,
                'JSON file to import for types for '.implode(', ', $this->getActions())
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var IntegrationHelper $integrationHelper */
        $integrationHelper = $this->getContainer()->get('mautic.helper.integration');
        $integrationObject = $integrationHelper->getIntegrationObject('Recombee');
        /** @var Translator $translator */
        $translator = $this->getContainer()->get('translator');

        if (!$integrationObject->getIntegrationSettings()->getIsPublished()) {
            return $output->writeln('<info>'.$translator->trans('mautic.plugin.recombee.disabled').'</info>');
        }

        /** @var RecombeeHelper $recombeeHelper */
        $recombeeHelper = $this->getContainer()->get('mautic.recombee.helper');

        $type = $input->getOption('type');

        if (empty($type)) {
            return $output->writeln(
                sprintf(
                    '<error>ERROR:</error> <info>'.$translator->trans(
                        'mautic.plugin.recombee.command.type.required',
                        ['%types' => implode(', ', $this->getTypes())]
                    ).'</info>'
                )
            );
        }

        if (!in_array($type, $this->getTypes())) {
            return $output->writeln(
                sprintf(
                    '<error>ERROR:</error> <info>'.$translator->trans(
                        'mautic.plugin.recombee.command.bad.type',
                        ['%type' => $type, '%types' => implode(', ', $this->getTypes())]
                    ).'</info>'
                )
            );
        }

        $file = $input->getOption('file');


        if (!in_array($type, $this->getTypes()) && empty($file)) {
            return $output->writeln(
                sprintf(
                    '<error>ERROR:</error> <info>'.$translator->trans(
                        'mautic.plugin.recombee.command.option.required',
                        ['%file' => 'file', '%actions' => implode(', ', $this->getActions())]
                    )
                )
            );
        }

        if ($type === 'items') {
            $integrationKeys = $integrationObject->getKeys();
            $file            = $integrationKeys['import_items'];
            if (empty($file)) {
                return $output->writeln(
                    sprintf(
                        '<error>ERROR:</error> <info>'.$translator->trans(
                            'mautic.plugin.recombee.command.option.required',
                            ['%file' => 'file', 'actions' => 'items']
                        )
                    )
                );
            }
        }

        if ($type !== 'contacts') {
            if (empty($file)) {
                return $output->writeln(
                    sprintf(
                        '<error>ERROR:</error> <info>'.$translator->trans(
                            'mautic.plugin.recombee.command.file.required'
                        )
                    )
                );
            }

            $json = file_get_contents($file);
            if (empty($json)) {
                return $output->writeln(
                    sprintf(
                        '<error>ERROR:</error> <info>'.$translator->trans(
                            'mautic.plugin.recombee.command.file.fail',
                            ['%file' => $file]
                        )
                    )
                );
            }
            $items = \GuzzleHttp\json_decode($json, true);

            if (empty($items) || ![$items]) {
                return $output->writeln(
                    sprintf(
                        '<error>ERROR:</error> <info>'.$translator->trans(
                            'mautic.plugin.recombee.command.json.fail',
                            ['%file' => $file]
                        )
                    )
                );
            }
        }


        // import Leads
        $criteria['integration']       = 'Recombee';
        $criteria['integrationEntity'] = 'users';
        $criteria['internalEntity']    = 'contacts';
        //$integrationEntity = $em->getRepository(IntegrationEntity::class)->findOneBy($criteria);
        /** @var ApiCommands $serviceApiCommands */
        $serviceApiCommands = $this->getContainer()->get('mautic.recombee.service.api.commands');
        switch ($type) {
            case "items":
                $serviceApiCommands->ImportItems($items);
                break;
            case "contacts":
                /** @var LeadModel $leadModel */
                $leadModel = $this->getContainer()->get('mautic.lead.model.lead');
                $leads     = $leadModel->getEntities(
                    [
                        'limit'              => 990,
                        'orderBy'            => 'l.id',
                        'orderByDir'         => 'asc',
                        'withPrimaryCompany' => true,
                        'withChannelRules'   => true,
                    ]
                );
                /** @var Lead $lead */
                $items = [];
                foreach ($leads as $lead) {
                    $items[$lead->getId()] = $lead->getProfileFields();
                }
                $serviceApiCommands->ImportUser($items);
                break;
        }

        $requestsPropertyValues = [];
        switch ($type) {
            case "views":
                $serviceApiCommands->callCommand('AddDetailView', $items);
                break;

            case "purchases":
                $serviceApiCommands->callCommand('AddPurchase', $items);
                break;

            case "carts":
                $serviceApiCommands->callCommand('AddCartAddition', $items);
                break;

            case "bookmarks":
                $serviceApiCommands->callCommand('AddBookmark', $items);
                break;
            case "ratings":
                $serviceApiCommands->callCommand('AddRating', $items);
                break;
            case "portions":
                $serviceApiCommands->callCommłand('SetViewPortion', $items);
                break;
        }

        try {
            if ($serviceApiCommands->hasCommandOutput()) {
                $this->displayCmdTextFromResult(
                    $serviceApiCommands->getCommandOutput(),
                    'user property values',
                    $output
                );
            }
        } catch
        (Ex\ResponseException $e) {
        }
    }


    /**
     * Display commands results
     *
     * @param array  $results
     * @param string $title
     */
    private function displayCmdTextFromResult(array $results, $title = '', OutputInterface $output)
    {
        $errors = [];
        foreach ($results as $result) {
            if (!empty($result['json']['error'])) {
                $errors[] = $result['json']['error'];
            }
        }
        // just add empty space
        if ($title != '') {
            $title .= ' ';
        }
        $errors = [];
        $output->writeln(sprintf('<info>Procesed '.$title.count($results).'</info>'));
        $output->writeln('Success '.$title.(count($results) - count($errors)));
        /*if (!empty($errors)) {
            $output->writeln('Errors '.$title.count($errors));
            $output->writeln($errors, true);
        }*/
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return array_merge($this->types, $this->actions);
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    public function file_get_contents_utf8($fn)
    {
        $content = file_get_contents($fn);

        return mb_convert_encoding(
            $content,
            'UTF-8',
            mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true)
        );
    }
}
