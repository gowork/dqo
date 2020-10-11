<?php declare(strict_types=1);

namespace tests\GW\DQO\Generator;

use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\Assert;
use function file_get_contents;

trait AstAssertions
{
    protected static function assertAstFilesEquals(string $expectedFile, string $generatedFile): void
    {
        self::assertAstEquals($expectedFile, file_get_contents($generatedFile));
    }

    protected static function assertAstEquals(string $expectedFile, string $generatedCode): void
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $astExpected = $parser->parse(file_get_contents($expectedFile));
        $ast = $parser->parse($generatedCode);

        $prettyPrinter = new Standard();

        $codeExpected = $prettyPrinter->prettyPrintFile($astExpected);
        $code = $prettyPrinter->prettyPrintFile($ast);

        Assert::assertEquals($codeExpected, $code);
    }
}
