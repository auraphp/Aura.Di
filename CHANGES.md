This release adds three new features.

- (ADD) LazyInclude and LazyRequire can now recieve a LazyValue as a filename, so that the filename is not resolved until the include/require is invoked.

- (ADD) Allow direct use of lazies in Lazy; cf PR #128.

- (ADD) Add a new LazyCallable type for injecting callable services; cf. PR #129.

- (CHG) LazyValue now resolves lazies itself; cf. PR #137.

- (ADD) Add a new LazyArray type for injecting arrays of lazy-resolved values; cf PR #138.

There are also carious documentation improvements, and the package now provides (via Composer) the virtual package `container-interop-implementation`.
