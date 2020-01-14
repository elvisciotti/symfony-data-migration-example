<?php

namespace App\Command;

use App\Entity\V1;
use App\Entity\V2;
use App\Repository\UserRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateV1ToV2Command extends Command
{
    protected static $defaultName = 'app:migrate-v1-to-v2';

    /**
     * @var EntityManager
     */
    private $em1;

    /**
     * @var EntityManager
     */
    private $em2;

    /**
     * @var UserRepository
     */
    private $userV2Repo;

    /**
     * MigrateV1ToV2Command constructor.
     * @param EntityManager $em
     */
    public function __construct(ManagerRegistry $em)
    {
        $this->em1 = $em->getManager('v1');
        $this->em2 = $em->getManager('v2');

        $this->userV2Repo = $this->em2->getRepository(V2\User::class);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addOption('truncate', null, InputOption::VALUE_NONE, 'truncate without asking');
    }

    private function truncateWithQuestion(InputInterface $input, OutputInterface $output)
    {
        // truncate v2 with confirmation
        if (!$input->getOption('truncate')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Data on database v2 needs to be truncated. Proceed (y/n)?', false);
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('Cancelled');

                return 1;
            }
        }

        $v2Connection = $this->em2->getConnection();
        $v2Connection->exec('SET foreign_key_checks = 0');
        $v2Connection->exec('TRUNCATE table contact;');
        $v2Connection->exec('TRUNCATE table employment;');
        $v2Connection->exec('TRUNCATE table organisation;');
        $v2Connection->exec('SET foreign_key_checks = 1');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->truncateWithQuestion($input, $output);

        // copy organisations
        foreach ($this->em1->getRepository(V1\Organisation::class)->findAll() as $orgV1) {
            /* @var V1\Organisation $orgV1 */
            $org = new V2\Organisation();
            $org->setName($orgV1->getName());
            $org->setCountryId($orgV1->getCountryId());
            $org->setCreatedOn($orgV1->getCreatedOn());
            $org->setUpdatedOn($orgV1->getUpdatedOn());
            $org->setPhone($orgV1->getPhone());
            $org->setEmail($orgV1->getEmail());
            $org->setAddress1($orgV1->getAddress1());
            $org->setAddress2($orgV1->getAddress2());
            $org->setAddress3($orgV1->getAddress3());
            $org->setCity($orgV1->getCity());
            $org->setPostalCode($orgV1->getPostalCode());
            $org->setWeb($orgV1->getWeb());
            $org->setCountryId($orgV1->getCountryId()); // no iBA nfo about potential external table
            if ($orgV1->getCreatedBy()) {
                $org->setCreatedBy($this->userV2Repo->getOrCreateByName($orgV1->getCreatedBy()));
            }
            if ($orgV1->getUpdatedBy()) {
                $org->setUpdatedBy($this->userV2Repo->getOrCreateByName($orgV1->getUpdatedBy()));
            }
            $org->setDeletedAt($orgV1->getDeleted() ? new \DateTime() : null);
            // more fields here
            $this->em2->persist($org);
        }

        // flush to make data available via repository method.
        // Could be avoided if more speed is needed
        $this->em2->flush();

        // copy old contact into employment AND contact tables
        foreach ($this->em1->getRepository(V1\OrganisationContact::class)->findAll() as $contactV1) {
            /* @var V1\OrganisationContact $contactV1 */

            // find org in new tables
            $orgName = $contactV1->getOrganisation()->getName();
            $org = $this->em2->getRepository(V2\Organisation::class)->findOneBy([
                'name' => $orgName,
            ]);

            // upsert contact if not existing already
            $contact = $this->em2->getRepository(V2\Contact::class)->findOneBy([
                'name' => $contactV1->getName(),
            ]);
            if (!$contact) {
                $contact = new V2\Contact($contactV1->getName(), (bool)$contactV1->getActive());
                $contact->setEmail($contactV1->getEmail());
                $contact->setPhone($contactV1->getPhone());
                $contact->getUpdatedOn($contactV1->getUpdatedOn());
                $contact->setCreatedOn($contactV1->getCreatedOn());
                $contact->setDeletedAt($contactV1->getDeleted() ? new \DateTime() : null);
                if ($contactV1->getCreatedBy()) {
                    $contact->setCreatedBy($this->userV2Repo->getOrCreateByName($contactV1->getCreatedBy()));
                }
                if ($contactV1->getUpdatedBy()) {
                    $contact->setUpdatedBy($this->userV2Repo->getOrCreateByName($contactV1->getUpdatedBy()));
                }
                $this->em2->persist($contact);
            }

            // upsert employment
            $employment = $this->em2->getRepository(V2\Employment::class)->findOneBy([
                'organisation' => $org,
                'contact' => $contact,
            ]);
            if (!$employment) {
                $employment = new V2\Employment($org, $contact, $contactV1->getTitle());
                $this->em2->persist($employment);
            }

            // flush data (could be moved to the end to increase speed)
            $this->em2->flush();
        }

        $output->writeln("Data migrated finished. Check db v2.");

        return 0;
    }
}
