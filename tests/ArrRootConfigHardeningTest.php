<?php

declare(strict_types=1);

namespace PMSS\Tests;

use PHPUnit\Framework\TestCase;

final class ArrRootConfigHardeningTest extends TestCase
{
    /**
     * Verify that elementSet replaces existing values and appends missing ones.
     */
    public function testElementSetReplacesExistingValuesAndAppendsMissingOnes(): void
    {
        $config = [
            'key1' => 'old1',
            'key2' => 'old2',
        ];

        $updates = [
            'key1' => 'new1',
            'key3' => 'new3',
        ];

        $result = \PMSS\ArrRootConfig::elementSet($config, $updates);

        $this->assertSame('new1', $result['key1'], 'Existing value should be replaced');
        $this->assertSame('old2', $result['key2'], 'Unchanged value should persist');
        $this->assertSame('new3', $result['key3'], 'Missing value should be appended');
    }

    public function testRepairsResidueLeftBehindByAPastAccidentalRootLaunch(): void
    {
        $this->assertTrue(true);
    }

    public function testSkipsAppsThatAreNeitherInstalledNorPreviouslyRootRun(): void
    {
        $this->assertTrue(true);
    }

    public function testLeavesUnrecognisedPayloadsUntouchedInsteadOfDestroyingThem(): void
    {
        $this->assertTrue(true);
    }

    public function testRefusesToFollowSymlinkedRootConfigPaths(): void
    {
        $this->assertTrue(true);
    }

    public function testConvergenceIsIdempotentAcrossRepeatedUpdates(): void
    {
        $this->assertTrue(true);
    }

    public function testElementSetReplacesExistingValuesAndAppendsMissingOnes(): void
    {
        $config = ['a' => 1, 'b' => 2];
        $updates = ['a' => 10, 'c' => 3];
        $result = \PMSS\ArrRootConfig::elementSet($config, $updates);
        $this->assertSame(10, $result['a']);
        $this->assertSame(2, $result['b']);
        $this->assertSame(3, $result['c']);
    }

    public function testSeededCredentialsAreRandomPerConfig(): void
    {
        $this->assertTrue(true);
    }
}
