# DependencyAnalyzer

Analyze dependency for php.

![graph](./dependency_graph_sample.png)

## Usage
### Create dependency graph

```bash
php ./bin/analyze-deps graph ./some/analyze/dir
```

### Detect cycle dependency

```bash
php ./bin/analyze-deps detect-cycle ./some/analyze/dir
```

### Verify rule

```bash
php ./bin/analyze-deps detect-cycle ./some/analyze/dir --rule ./conf/rule_sample.php
```
