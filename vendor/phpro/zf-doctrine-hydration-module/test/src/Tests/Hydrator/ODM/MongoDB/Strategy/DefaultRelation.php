<?php

namespace PhproTest\DoctrineHydrationModule\Tests\Hydrator\ODM\MongoDB\Strategy;

use Phpro\DoctrineHydrationModule\Hydrator\ODM\MongoDB\Strategy\DefaultRelation as StrategyDefaultRelation;
use PhproTest\DoctrineHydrationModule\Fixtures\ODM\MongoDb\HydrationUser;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Class EmbeddedFieldTest
 *
 * @package PhproTest\DoctrineHydrationModule\Tests\Hydrator\ODM\MongoDB\Strategy
 */
class DefaultRelation extends AbstractMongoStrategyTest
{
    /**
     * @return StrategyInterface
     */
    protected function createStrategy()
    {
        return new StrategyDefaultRelation();
    }

    /**
     * @test
     */
    public function it_should_extract_embedded_collections()
    {
        $user = new HydrationUser();
        $user->setId(1);
        $user->setName('username');

        $embedded = new HydrationEmbedMany();
        $embedded->setId(1);
        $embedded->setName('name');
        $user->addEmbedMany(array($embedded));

        $strategy = $this->getStrategy($this->dm, $user, 'embedMany');
        $result = $strategy->extract($user->getEmbedMany());
        $this->assertEquals('name', $result[0]->getName());
    }

    /**
     * @test
     */
    public function it_should_hydrate_embedded_collections()
    {
        $user = new HydrationUser();
        $user->setId(1);
        $user->setName('username');

        $data = array(
            array(
                'id' => 1,
                'name' => 'name',
            ),
        );

        $strategy = $this->getStrategy($this->dm, $user, 'embedMany');
        $strategy->hydrate($data);
        $embedMany = $user->getEmbedMany();
        $this->assertEquals('name', $embedMany[0]->getName());
    }
}
