# mitra-backend
The backend powering Mitra - the distributed social network.

## Install dependencies
```
$ cd docker && docker-compose run composer install
```

## Run
### Environment
```
$ cd docker && docker-compose up
```

### Code style
```
$ make code-style
```

### Static analysis
```
$ make static-analysis
```

### Tests
```
$ make test              # unit and integration tests
$ make test-unit         # unit tests only
$ make test-integration  # integration tests only
```

### Coverage
```
$ make coverage
```

## Access
Once the Mitra backend is up and running it is reachable over:

```
http://localhost:1337
```
