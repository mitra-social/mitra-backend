# mitra-backend
The backend powering Mitra - the distributed social network.

## Install dependencies
```
$ cd docker && docker-compose run composer install
```

## Run

### Environment

#### Prerequisites
Add user id to your shell init file

Add the following to your .bashrc (or to the respective dot file if you don't use bash)

```
export USER_ID=$(id -u)
```

Create an rsa key with the following instructions
```
$ ssh-keygen 
```

⚠️ Be sure to source your .bashrc or open a new console

#### Start up docker
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

### Authentication

To authenticate against the API you can use a JWT token with the following payload:

```
{
    "userId": "your uuid"
    "iat": "current unix timestamp"
}
```

and sign it with the `HS256` algorithm by using the development secret `s3kr3T!`. You can use [jwt.io](https://jwt.io/)
to generate such a development JWT token.
