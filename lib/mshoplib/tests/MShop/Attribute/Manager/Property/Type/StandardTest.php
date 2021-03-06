<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2020
 */


namespace Aimeos\MShop\Attribute\Manager\Property\Type;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $editor = '';


	protected function setUp() : void
	{
		$this->editor = \TestHelperMShop::getContext()->getEditor();
		$manager = \Aimeos\MShop\Attribute\Manager\Factory::create( \TestHelperMShop::getContext() );
		$this->object = $manager->getSubManager( 'property' )->getSubManager( 'type' );
	}


	protected function tearDown() : void
	{
		unset( $this->object );
	}


	public function testClear()
	{
		$this->assertInstanceOf( \Aimeos\MShop\Common\Manager\Iface::class, $this->object->clear( [-1] ) );
	}


	public function testCreateItem()
	{
		$this->assertInstanceOf( \Aimeos\MShop\Common\Item\Type\Iface::class, $this->object->createItem() );
	}


	public function testGetResourceType()
	{
		$this->assertContains( 'attribute/property/type', $this->object->getResourceType() );
	}


	public function testGetSearchAttributes()
	{
		foreach( $this->object->getSearchAttributes() as $attribute ) {
			$this->assertInstanceOf( \Aimeos\MW\Criteria\Attribute\Iface::class, $attribute );
		}
	}


	public function testGetItem()
	{
		$search = $this->object->createSearch()->setSlice( 0, 1 );
		$conditions = array(
			$search->compare( '==', 'attribute.property.type.code', 'size' ),
			$search->compare( '==', 'attribute.property.type.editor', $this->editor )
		);
		$search->setConditions( $search->combine( '&&', $conditions ) );

		$results = $this->object->searchItems( $search )->toArray();

		if( ( $expected = reset( $results ) ) === false )
		{
			throw new \RuntimeException( 'No property type item found.' );
		}

		$actual = $this->object->getItem( $expected->getId() );

		$this->assertEquals( $expected, $actual );
	}


	public function testSaveUpdateDeleteItem()
	{
		$search = $this->object->createSearch();
		$search->setConditions( $search->compare( '==', 'attribute.property.type.editor', $this->editor ) );
		$results = $this->object->searchItems( $search )->toArray();

		if( ( $item = reset( $results ) ) === false ) {
			throw new \RuntimeException( 'No type item found' );
		}

		$item->setId( null );
		$item->setCode( 'unitTestSave' );
		$resultSaved = $this->object->saveItem( $item );
		$itemSaved = $this->object->getItem( $item->getId() );

		$itemExp = clone $itemSaved;
		$itemExp->setCode( 'unitTestSave2' );
		$resultUpd = $this->object->saveItem( $itemExp );
		$itemUpd = $this->object->getItem( $itemExp->getId() );

		$this->object->deleteItem( $itemSaved->getId() );


		$this->assertTrue( $item->getId() !== null );
		$this->assertEquals( $item->getId(), $itemSaved->getId() );
		$this->assertEquals( $item->getSiteId(), $itemSaved->getSiteId() );
		$this->assertEquals( $item->getCode(), $itemSaved->getCode() );
		$this->assertEquals( $item->getDomain(), $itemSaved->getDomain() );
		$this->assertEquals( $item->getLabel(), $itemSaved->getLabel() );
		$this->assertEquals( $item->getStatus(), $itemSaved->getStatus() );

		$this->assertEquals( $this->editor, $itemSaved->getEditor() );
		$this->assertRegExp( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $itemSaved->getTimeCreated() );
		$this->assertRegExp( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $itemSaved->getTimeModified() );

		$this->assertEquals( $itemExp->getId(), $itemUpd->getId() );
		$this->assertEquals( $itemExp->getSiteId(), $itemUpd->getSiteId() );
		$this->assertEquals( $itemExp->getCode(), $itemUpd->getCode() );
		$this->assertEquals( $itemExp->getDomain(), $itemUpd->getDomain() );
		$this->assertEquals( $itemExp->getLabel(), $itemUpd->getLabel() );
		$this->assertEquals( $itemExp->getStatus(), $itemUpd->getStatus() );

		$this->assertEquals( $this->editor, $itemUpd->getEditor() );
		$this->assertEquals( $itemExp->getTimeCreated(), $itemUpd->getTimeCreated() );
		$this->assertRegExp( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $itemUpd->getTimeModified() );

		$this->assertInstanceOf( \Aimeos\MShop\Common\Item\Iface::class, $resultSaved );
		$this->assertInstanceOf( \Aimeos\MShop\Common\Item\Iface::class, $resultUpd );

		$this->expectException( \Aimeos\MShop\Exception::class );
		$this->object->getItem( $itemSaved->getId() );
	}


	public function testSearchItems()
	{
		$search = $this->object->createSearch();

		$expr = [];
		$expr[] = $search->compare( '!=', 'attribute.property.type.id', null );
		$expr[] = $search->compare( '!=', 'attribute.property.type.siteid', null );
		$expr[] = $search->compare( '==', 'attribute.property.type.domain', 'attribute' );
		$expr[] = $search->compare( '==', 'attribute.property.type.code', 'size' );
		$expr[] = $search->compare( '==', 'attribute.property.type.label', 'Size' );
		$expr[] = $search->compare( '>=', 'attribute.property.type.position', 0 );
		$expr[] = $search->compare( '==', 'attribute.property.type.status', 1 );
		$expr[] = $search->compare( '>=', 'attribute.property.type.mtime', '1970-01-01 00:00:00' );
		$expr[] = $search->compare( '>=', 'attribute.property.type.ctime', '1970-01-01 00:00:00' );
		$expr[] = $search->compare( '==', 'attribute.property.type.editor', $this->editor );

		$search->setConditions( $search->combine( '&&', $expr ) );
		$results = $this->object->searchItems( $search )->toArray();
		$this->assertEquals( 1, count( $results ) );
	}


	public function testSearchItemsAll()
	{
		$total = 0;
		$search = $this->object->createSearch();
		$conditions = array(
			$search->compare( '=~', 'attribute.property.type.code', '' ),
			$search->compare( '==', 'attribute.property.type.editor', $this->editor )
		);
		$search->setConditions( $search->combine( '&&', $conditions ) );
		$search->setSortations( [$search->sort( '-', 'attribute.property.type.position' )] );
		$search->setSlice( 0, 2 );
		$items = $this->object->searchItems( $search, [], $total )->toArray();

		$this->assertEquals( 2, count( $items ) );
		$this->assertEquals( 3, $total );

		$pos = 2;
		foreach( $items as $itemId => $item )
		{
			$this->assertEquals( $itemId, $item->getId() );
			$this->assertEquals( $pos--, $item->getPosition() );
		}
	}


	public function testGetSubManager()
	{
		$this->expectException( \Aimeos\MShop\Exception::class );
		$this->object->getSubManager( 'unknown' );
	}
}
