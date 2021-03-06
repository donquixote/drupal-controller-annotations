<?php

namespace Drupal\Tests\controller_annotations\Unit\Request\ParamConverter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\controller_annotations\Request\ParamConverter\NodeParamConverter;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Mockery as m;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group controller_annotations
 */
class NodeParamConverterTest extends UnitTestCase
{
    private function getNodeParamConverter()
    {
        $node = m::mock(Node::class);

        $entityInterface = m::mock(EntityInterface::class);
        $entityInterface->shouldReceive('load')->andReturn($node);

        $entityTypeManager = m::mock(EntityTypeManager::class);
        $entityTypeManager->shouldReceive('getStorage')->andReturn($entityInterface);

        return new NodeParamConverter($entityTypeManager);
    }

    public function testSupports()
    {
        $paramConverter = m::mock(ParamConverter::class);
        $paramConverter->shouldReceive('getClass')->once()->andReturn(Node::class);
        $this->assertTrue($this->getNodeParamConverter()->supports($paramConverter));
    }

    public function testNotSupports()
    {
        $paramConverter = m::mock(ParamConverter::class);
        $paramConverter->shouldReceive('getClass')->once()->andReturn(self::class);
        $this->assertFalse($this->getNodeParamConverter()->supports($paramConverter));
    }

    public function testApply()
    {
        $name = 'test';
        $request = new Request();
        $request->attributes->set($name, 1);

        $node = m::mock(Node::class);

        $paramConverter = m::mock(ParamConverter::class);
        $paramConverter->shouldReceive('getClass')->once()->andReturn(Node::class);
        $paramConverter->shouldReceive('getName')->once()->andReturn($name);
        $paramConverter->shouldReceive('getOptions')->once()->andReturn([]);

        $this->assertTrue($this->getNodeParamConverter()->supports($paramConverter));
        $this->getNodeParamConverter()->apply($request, $paramConverter);

        $this->assertTrue($request->attributes->has($name));
        $this->assertEquals($node, $request->attributes->get($name));
    }

    public function testApplyWithBundle()
    {
        $id = 1;
        $bundle = 'article';

        $node = m::mock(Node::class);
        $node->shouldReceive('bundle')->once()->andReturn($bundle);

        $entityInterface = m::mock(EntityInterface::class);
        $entityInterface->shouldReceive('load')->withArgs([$id])->andReturn($node);

        $entityTypeManager = m::mock(EntityTypeManager::class);
        $entityTypeManager->shouldReceive('getStorage')->withArgs(['node'])->andReturn($entityInterface);

        $nodeParamConverter = new NodeParamConverter($entityTypeManager);

        $name = 'test';
        $request = new Request();
        $request->attributes->set($name, $id);

        $paramConverter = m::mock(ParamConverter::class);
        $paramConverter->shouldReceive('getClass')->once()->andReturn(Node::class);
        $paramConverter->shouldReceive('getName')->once()->andReturn($name);
        $paramConverter->shouldReceive('getOptions')->once()->andReturn(['bundle' => $bundle]);

        $this->assertTrue($nodeParamConverter->supports($paramConverter));
        $nodeParamConverter->apply($request, $paramConverter);

        $this->assertTrue($request->attributes->has($name));
        $this->assertEquals($node, $request->attributes->get($name));
    }

    public function testApplyWithWrongBundle()
    {
        $this->setExpectedException(NotFoundHttpException::class);

        $id = 1;
        $bundle = 'article';

        $node = m::mock(Node::class);
        $node->shouldReceive('bundle')->once()->andReturn('not_an_article');

        $entityInterface = m::mock(EntityInterface::class);
        $entityInterface->shouldReceive('load')->withArgs([$id])->andReturn($node);

        $entityTypeManager = m::mock(EntityTypeManager::class);
        $entityTypeManager->shouldReceive('getStorage')->withArgs(['node'])->andReturn($entityInterface);

        $nodeParamConverter = new NodeParamConverter($entityTypeManager);

        $name = 'test';
        $request = new Request();
        $request->attributes->set($name, $id);

        $paramConverter = m::mock(ParamConverter::class);
        $paramConverter->shouldReceive('getClass')->once()->andReturn(Node::class);
        $paramConverter->shouldReceive('getName')->once()->andReturn($name);
        $paramConverter->shouldReceive('getOptions')->once()->andReturn(['bundle' => $bundle]);

        $this->assertTrue($nodeParamConverter->supports($paramConverter));
        $nodeParamConverter->apply($request, $paramConverter);
    }

    public function testApplyOptionalWhenEmpty()
    {
        $id = 1;
        $bundle = 'article';

        $entityInterface = m::mock(EntityInterface::class);
        $entityInterface->shouldReceive('load')->withArgs([$id])->andReturn(null);

        $entityTypeManager = m::mock(EntityTypeManager::class);
        $entityTypeManager->shouldReceive('getStorage')->withArgs(['node'])->andReturn($entityInterface);

        $nodeParamConverter = new NodeParamConverter($entityTypeManager);

        $name = 'test';
        $request = new Request();
        $request->attributes->set($name, $id);

        $paramConverter = m::mock(ParamConverter::class);
        $paramConverter->shouldReceive('getClass')->once()->andReturn(Node::class);
        $paramConverter->shouldReceive('getName')->once()->andReturn($name);
        $paramConverter->shouldReceive('getOptions')->once()->andReturn(['bundle' => $bundle]);
        $paramConverter->shouldReceive('isOptional')->once()->andReturn(true);

        $this->assertTrue($nodeParamConverter->supports($paramConverter));
        $nodeParamConverter->apply($request, $paramConverter);

        $this->assertTrue($request->attributes->has($name));
        $this->assertEquals(null, $request->attributes->get($name));
    }

    public function testApplyWithoutAttribute()
    {
        $id = 1;
        $bundle = 'article';

        $entityInterface = m::mock(EntityInterface::class);
        $entityInterface->shouldReceive('load')->withArgs([$id])->andReturn(null);

        $entityTypeManager = m::mock(EntityTypeManager::class);
        $entityTypeManager->shouldReceive('getStorage')->withArgs(['node'])->andReturn($entityInterface);

        $nodeParamConverter = new NodeParamConverter($entityTypeManager);

        $name = 'test';
        $request = new Request();

        $paramConverter = m::mock(ParamConverter::class);
        $paramConverter->shouldReceive('getClass')->once()->andReturn(Node::class);
        $paramConverter->shouldReceive('getName')->once()->andReturn($name);
        $paramConverter->shouldReceive('getOptions')->never()->andReturn(['bundle' => $bundle]);

        $this->assertTrue($nodeParamConverter->supports($paramConverter));
        $this->assertFalse($nodeParamConverter->apply($request, $paramConverter));
    }

    public function tearDown()
    {
        m::close();
    }
}
