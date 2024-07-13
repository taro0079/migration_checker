<?php


use PHPUnit\Framework\TestCase;

require '../src/migration_checker.php';

class EntityParserTest extends TestCase
{
    public function testGetTableName()
    {
        $targetClass = new EntityParser();
        $result = $targetClass->getTableName();
        $this->assertSame('trn_payment_invoice', $result);
    }

    public function testGetProperties(): void
    {
        $targetClass = new EntityParser();
        $result = $targetClass->getProperties();
        $this->assertCount(23, $result);
    }

    public function testCreateComplete(): void
    {
        $targetClass = new EntityParser();
        $result = $targetClass->createSets();
        $this->assertCount(26, $result);
    }
}
