<?php


use PHPUnit\Framework\TestCase;

require '../src/migration_checker.php';
class EntityParserTest extends TestCase
{
    public function testGetTableName()
    {
        $targetClass = new EntityParser('../test.php');
        $result = $targetClass->getTableName();
        $this->assertSame('trn_payment_invoice', $result);
    }

    public function testGetProperties():void
    {
        $targetClass = new EntityParser('../test.php');
        $result = $targetClass->getProperties();
        $this->assertCount(23, $result);
    }
}
