# debug-toolbar
An improvement a day keeps the doctor away

### Install

Via composer:

```
composer require --dev "alsvanzelf/debugtoolbar:dev-master"
```

### Usage

- Copy `demo/dist/` to a directory in your own application and serve it via the web.
- Copy `demo/debug-display.php` to a directory in your own application and serve it via the web.
- Setup a logger (i.e. [Monolog](https://github.com/Seldaek/monolog)) storing the results in a database.
- Adjust your application's page rendering in between rendering and sending it to the browser:
	- to track data of the request: `$logId = Log::track($logger)`,
	- and add the toggler to the rendered body: `(new Toggler($logId))->render()`.
- Optionally pass a `$scriptUrl` and `$displayUrl` to the `Toggler`'s constructor with the urls of the `dist` directory and `debug-display.php` copied before.
- Adjust the `debug-display.php` to fetch the data from the database used by the logger.
- When using PDO: call `PDOPart::trackExecutedStatement($statement, $binds)` after executing a PDO statement.
- When using Twig: [setup it's profiler](https://twig.symfony.com/doc/2.x/api.html#profiler-extension) and call `TwigPart::trackProfiler($profiler)`.
