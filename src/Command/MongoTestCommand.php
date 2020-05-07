<?php

namespace App\Command;

use App\Entity\Mongo\Product;
use Doctrine\Common\Util\Debug;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MongoTestCommand extends Command
{
    protected static $defaultName = 'app:mongo-test';

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * MongoTestCommand constructor.
     *
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Mongo Db test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $product = new Product();
        $product->setName('Product '.time());
        $product->setPrice(rand(1, 1000));

        $this->dm->persist($product);
        $this->dm->flush();

        foreach ($this->dm->getRepository(Product::class)->findAll() as $product) {
            Debug::dump($product);
        }

        return 0;
    }
}
