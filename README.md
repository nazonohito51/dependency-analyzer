# DependencyAnalyzer

Analyze dependency map for php.

## Description

Dependency analyzer help you to keeping clean your architecture.
If you start to managing dependencies between classes likely [Clean Architecture]() or [Layered Architecture](),
inspecting dependency between classes by eye is very difficult in PHP.
This library analyze dependencies in your repository, and take some way of using it to you.

Example: create graph, verify dependencies, detect cycle path...

このライブラリの強みはそれだけではない、依存関係のルールをルールファイルという形で明示し、チームと共有でき、チームの暗黙知が動く法則として機能することだ。

## Basic Usages
### Create dependency graph

![graph](./dependency_graph_sample.png)

```bash
php ./bin/analyze-deps graph ./some/analyze/dir
```

Analysis dependency map and create graph. Now, dependency analyzer support only puml format.

### Detect cycle dependency

```bash
php ./bin/analyze-deps detect-cycle ./some/analyze/dir
```

### Verify rule

```bash
php ./bin/analyze-deps detect-cycle ./some/analyze/dir --rule ./conf/rule_sample.php
```
