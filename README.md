# PhpSoftBox API

Компонент описывает HTTP API, его публичные ошибки и правила выбора опубликованной
версии запроса. Он не извлекает версию из URL и не зависит от структуры routes
проекта.

## Версии

Проект объявляет опубликованные версии enum-ом:

```php
enum CatalogApiVersionEnum: string implements ApiVersionEnumInterface
{
    case VERSION_1_0_0 = '1.0.0';
    case VERSION_1_1_0 = '1.1.0';

    public function version(): string
    {
        return $this->value;
    }
}
```

Policy содержит точный список и default:

```php
$policy = new EnumApiVersionPolicy(
    supportedVersions: CatalogApiVersionEnum::cases(),
    defaultVersion: CatalogApiVersionEnum::VERSION_1_1_0,
);
```

Отсутствующий или пустой header выбирает default. Синтаксически корректная, но
неопубликованная версия отклоняется: промежуточный диапазон не поддерживается.

```php
$middleware = new ApiVersionMiddleware(
    resolver: new HeaderApiVersionResolver('X-CATALOG-API-VERSION'),
    factory: new DefaultApiRequestVersionFactory($policy),
    headers: new ApiVersionResponseHeaders(
        selected: 'X-CATALOG-API-VERSION',
        minimum: 'X-CATALOG-API-VERSION-MIN',
        latest: 'X-CATALOG-API-VERSION-LATEST',
    ),
);
```

Middleware сохраняет `ApiRequestVersionInterface` в request attributes и
добавляет selected/minimum/latest headers и `Vary` в response.

## Ошибки

`ApiErrorCatalog` объединяет общие и специфичные определения и отклоняет
конфликтующие codes:

```php
$description = ApiDescription::create('catalog')
    ->withCommonErrors(CommonApiErrorEnum::cases())
    ->withErrors(CatalogApiErrorEnum::cases())
    ->withVersionPolicy($policy)
    ->build();
```

Endpoint может ссылаться только на зарегистрированные определения через
`#[ApiErrors(...)]`. Перед генерацией документации ссылки следует проверить через
`ApiErrorCatalog::requireAll()`.

Для JSON runtime responses `phpsoftbox/application` предоставляет
`CodedHttpException`. Предметные исключения преобразуются в него через
`ExceptionMapperRegistry`; публичные status, code, title и message берутся из
`ApiErrorDefinition`.

Если `phpsoftbox/application` установлен, готовый
`ApiVersionExceptionMapper` преобразует ошибки negotiation в
`CodedHttpException`, добавляя minimum/latest headers и `Vary`. Application
указан как optional integration dependency: core versioning остаётся PSR-only.

## Подключение в проекте

1. Объявите enum опубликованных версий и policy.
2. Соберите `ApiDescription`, зарегистрировав common и project-specific errors.
3. Добавьте `ApiVersionMiddleware` только на versioned route group.
4. Зарегистрируйте `ApiVersionExceptionMapper` в application
   `ExceptionMapperRegistry`.
5. Передавайте выбранную версию в Action как `ApiRequestVersionInterface` либо
   проектный наследник `AbstractApiRequestVersion` с именованными feature gates.

Major в URL не входит в этот контракт. `/v1`, namespace контроллеров и набор
маршрутов остаются проектным routing-соглашением; опубликованную полную версию
определяет только policy подключённого API.

## Auth audience

Если приложение выпускает отдельные credentials для нескольких API, стабильный
`ApiDescription::id` следует использовать как credential audience. Например,
credential с audience `catalog` принимается route group API `catalog`, но не API
`warehouse`.

API version и major в URL в audience не входят. Компонент `Api` только задаёт
идентификатор; выдача, хранение и проверка credentials остаются ответственностью
`phpsoftbox/auth` и проектного middleware.

Внешнему проекту после обновления также нужно учесть framework-изменения:

- JSON-ошибки Application получили обязательный `code`;
- Auth credential storage переименован и использует `subjectId`/`purpose`/`audience`;
- `X-RateLimit-Reset` стал абсолютным Unix timestamp;
- production rate limit следует переключить с `SimpleCacheRateLimiter` на
  атомарный backend.
