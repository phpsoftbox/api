<?php

declare(strict_types=1);

namespace PhpSoftBox\Api\Tests;

use InvalidArgumentException;
use PhpSoftBox\Api\Description\ApiDescription;
use PhpSoftBox\Api\Description\ApiDescriptionBuilder;
use PhpSoftBox\Api\Error\ApiErrorCatalog;
use PhpSoftBox\Api\Error\ApiErrorDefinition;
use PhpSoftBox\Api\Error\CommonApiErrorEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiDescription::class)]
#[CoversClass(ApiDescriptionBuilder::class)]
#[CoversClass(ApiErrorCatalog::class)]
#[CoversMethod(ApiDescription::class, 'create')]
#[CoversMethod(ApiDescriptionBuilder::class, 'build')]
#[CoversMethod(ApiErrorCatalog::class, 'register')]
#[CoversMethod(ApiErrorCatalog::class, 'requireAll')]
final class ApiErrorCatalogTest extends TestCase
{
    /**
     * Проверяет объединение общих и прикладных ошибок при сборке единого описания API.
     *
     * @see ApiDescription::create()
     * @see ApiDescriptionBuilder::build()
     * @see ApiErrorCatalog::has()
     */
    #[Test]
    public function descriptionCombinesCommonAndApiSpecificErrors(): void
    {
        $description = ApiDescription::create('catalog')
            ->withCommonErrors(CommonApiErrorEnum::cases())
            ->withErrors([
                new ApiErrorDefinition(
                    'unsupported_catalog_filter',
                    400,
                    'Unsupported catalog filter',
                    'The supplied catalog filter is not supported.',
                ),
            ])
            ->build();

        self::assertTrue($description->errors->has('validation_failed'));
        self::assertTrue($description->errors->has('unsupported_catalog_filter'));
    }

    /**
     * Проверяет, что каталог ошибок готового описания API нельзя изменить после сборки.
     *
     * @see ApiDescriptionBuilder::build()
     * @see ApiErrorCatalog::register()
     */
    #[Test]
    public function builtDescriptionHasImmutableErrorCatalog(): void
    {
        $description = ApiDescription::create('catalog')->build();

        $this->expectException(InvalidArgumentException::class);
        $description->errors->register(new ApiErrorDefinition('late_error', 400, 'Late error', 'Late definition.'));
    }

    /**
     * Проверяет, что одинаковый код с различающимися определениями обнаруживается при регистрации.
     *
     * @see ApiErrorCatalog::register()
     */
    #[Test]
    public function conflictingDefinitionsFailFast(): void
    {
        $catalog = new ApiErrorCatalog([
            new ApiErrorDefinition('conflict', 409, 'Conflict', 'First definition.'),
        ]);

        $this->expectException(InvalidArgumentException::class);
        $catalog->register(new ApiErrorDefinition('conflict', 400, 'Conflict', 'Other definition.'));
    }

    /**
     * Проверяет, что операция не может ссылаться на отсутствующий в каталоге код ошибки.
     *
     * @see ApiErrorCatalog::requireAll()
     */
    #[Test]
    public function unknownOperationErrorFailsFast(): void
    {
        $catalog = new ApiErrorCatalog(CommonApiErrorEnum::cases());

        $this->expectException(InvalidArgumentException::class);
        $catalog->requireAll(['missing_error']);
    }
}
