# DependencyAnalyzer

Analyze/Verify dependency map for php.

[![Latest Stable Version](https://poser.pugx.org/nazonohito51/dependency-analyzer/version)](https://packagist.org/packages/nazonohito51/dependency-analyzer)
[![Build Status](https://scrutinizer-ci.com/g/nazonohito51/dependency-analyzer/badges/build.png?b=master)](https://scrutinizer-ci.com/g/nazonohito51/dependency-analyzer/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nazonohito51/dependency-analyzer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nazonohito51/dependency-analyzer/?branch=master)

## Description
Dependency analyzer help you to keep cleaning your architecture.

If you start to managing dependencies between classes likely [Clean Architecture](http://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html) or Layered Architecture, you will aware inspecting dependency between classes by eyes is very difficult in PHP.
This library analyze dependencies in your repository, and take some way of using it to you.(Example: Create UML graph, Verify by your rule, Detect cycle path)

## Basic Usages
This library have several functions.
If you have error when use them, see [Trouble Shooting](https://github.com/nazonohito51/dependency-analyzer/wiki/Trouble-shooting).

### Create dependency graph

![graph](./dependency_graph_sample.png)

```bash
php vendor/bin/analyze-deps graph --output ./graph.puml ./some/analyze/dir1 ./some/analyze/dir2
```

Analysis dependency map and create graph. Now, dependency analyzer support only [Plant UML](https://github.com/plantuml/plantuml) format.

Maybe, your graph will have many classes, and is very complex! If you need to simplify your graph, see [Advanced Usage](#advanced-usages). 

### Verify your dependency rule
In Clean Architecture, there is dependency rules between classes.
You can define your dependency rule, and this library will detect rule violation in your repository and notify them to you.

First, you can define your rule by php file, like below:

```php
<?php
// ./your_rule_file.php

return [
    'layer dependency rule' => [                // name of your rule
        'domain_layer' => [                     // component name
            'define' => ['\Acme\Domain\\'],     // component definition by FQSEN
            'depender' => ['application_layer'] // rule of component dependency, for depender
        ],
        'application_layer' => [
            'define' => ['\Acme\Application\\'],
            'depender' => ['controller_layer']
        ],
        'controller_layer' => [
            'define' => ['\App\\', '!\App\Providers\\']
        ]
    ],
//    'some more rules' => [
//        'SomeComponent' => ['...'],
//        '...' => []
//    ]
];
```

`'component'` is a group of classes. (About class name matching rule, [see wiki](https://github.com/nazonohito51/dependency-analyzer/wiki/Class-name-matching).)
`'depender'` is classes/components that depend on component.
`'dependee'` is classes/components that is depended on component.
You can restrict depender/dependee.
Then, you can verify your repository like this:

```bash
php vendor/bin/analyze-deps verify --rule ./your_rule_file.php ./some/analyze/dir1 ./some/analyze/dir2
```

If there is rule violation, notify you of them.

```bash
layer dependency rule
+------------------------------------+------------------+----+---------------------------+--------------+
| depender                           | component        |    | dependee                  | component    |
+------------------------------------+------------------+----+---------------------------+--------------+
| App\UseCaseRequests\GetUserRequest | controller_layer | -> | Acme\Domain\Entities\User | domain_layer |
+------------------------------------+------------------+----+---------------------------+--------------+
```

More detail about rule file, [see wiki](https://github.com/nazonohito51/dependency-analyzer/wiki/Detail-of-rule-file).
More example about rule file, [see this repository rule file](https://github.com/nazonohito51/dependency-analyzer/blob/master/conf/this_repository_rule.php).

### Verify your dependency rule by phpdoc

In verify dependency, you can use phpdoc too.
You can restrict depener of class by writing `@da-internal`.

```php
<?php
namespace Acme\Domain\Entities;

/**
 * Don't touch this class in \App\.
 * @da-internal !\App\
 */
class User
{
    /**
     * Don't use `new User();` in places other than Repository.
     * @da-internal \Acme\Domain\Repositories\
     */
    public function __construct()
    {
        // ...
    }

    /**
     * Don't touch password in places other than authenticate functions.
     * @da-internal \Acme\Application\Authenticator
     * @da-internal \Acme\Application\UseCases\UpdatePasswordInteractor
     */
    public function getPassword()
    {
        // ...
    }
}
```

Then, you can verify your repository. (command is same as [Verify your dependency rule](#verify-your-dependency-rule))
Of course, you can use rule file and phpdoc at same time.
In the process of analyzing repository, this library collect phpdoc, and verify your repository.
If there is rule violation, notify you of them.

```bash
phpdoc in \Acme\Domain\Entities\User
+-----------------------------------------------------------+-----------+----+-------------------------------------------+-----------+
| depender                                                  | component |    | dependee                                  | component |
+-----------------------------------------------------------+-----------+----+-------------------------------------------+-----------+
| \App\Http\Controllers\UserController                      | other     | -> | \Acme\Domain\Entities\User                | phpdoc    |
| \App\Http\Controllers\UserController::show()              | other     | -> | \Acme\Domain\Entities\User::getId()       | phpdoc    |
| \App\UseCaseRequests\GetUserRequest::__construct()        | other     | -> | \Acme\Domain\Entities\User::__construct() | phpdoc    |
| \Acme\Application\UseCases\CreateUserInteractor::handle() | other     | -> | \Acme\Domain\Entities\User::getPassword() | phpdoc    |
+-----------------------------------------------------------+-----------+----+-------------------------------------------+-----------+
```

More detail about phpdoc, [see wiki](https://github.com/nazonohito51/dependency-analyzer/wiki/More-detail-of-phpdoc).

### Detect cycle dependency

In [Acyclic dependencies principle](https://en.wikipedia.org/wiki/Acyclic_dependencies_principle), dependencies graph should have no cycles.
You can detect cycles like this:

```bash
php vendor/bin/analyze-deps detect-cycle ./some/analyze/dir
```

```bash
+---------------------------------------------+----+
| class                                       |    |
+---------------------------------------------+----+
| App\Http\Controllers\Api\UserController     | -> |
| Acme\Application\UseCases\GetUserInteractor | -> |
| Acme\Domain\Entities\User                   | -> |
| App\Http\Controllers\UserController         | -> |
| App\UseCaseRequests\GetUserRequest          | -> |
| App\Http\Controllers\Api\UserController     |    |
+---------------------------------------------+----+
```

## What is dependency?
Dependency is knowledge of interface that is had by class.
In classes collaboration, every class always have knowledge of interface of other classes.
If a interface is changed, classes what have knowledge of that interface is must fixed.

Dependency is created by below php syntaxes.

* extends/implements
* use trait
* new object
* type hinting (defined by phpdoc too)
* return type declaration (defined by phpdoc too)
* fetch public property/constant
* call public method
* class class method
* throw
* catch
* instanceof
* foreach access
* array dim access
* call function

This library analyze those syntaxes by using [PHPStan](https://github.com/phpstan/phpstan), and create dependency graph.
If you want to know detail, [see example](https://github.com/nazonohito51/dependency-analyzer/blob/master/tests/fixtures/all_theme/AllTheme.php).

## Advanced Usages
### Create dependency graph
TBD...

* rule file
* namespace
* group
* comment

### Verify your dependency rule
TBD...

* namespace rule
* magic keyword

## TODO
- [x] Display error details
- [ ] README
  - [ ] graph
  - [x] wiki
- [x] Analyze Facade
- [x] Response object & format
  - [x] use table format
- [ ] comment of Plant UML
- [x] fix namespace pattern matting(adjust file pattern matting)
  - [x] only !\Hoge\Fuga
- [ ] Graph format(another puml)
- [ ] original rule logic
  - [x] remove dependency to vertex, edge
- [ ] Improve performance by using cache
- [x] Analyze per class member(property/method/constant)
