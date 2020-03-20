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
Generating public/private rsa key pair.
Enter file in which to save the key (/Users/fgervasi/.ssh/id_rsa): // press <ENTER>
Enter passphrase (empty for no passphrase): // press <ENTER> 
Enter same passphrase again: // press <ENTER>
Your identification has been saved in /Users/fgervasi/.ssh/id_rsa.
Your public key has been saved in /Users/fgervasi/.ssh/id_rsa.pub.
The key fingerprint is:
SHA256:w56BPoD4NuHAwUxKKjJoeb6LvoFqSmM5j1EnOqiPFdY fgervasi@Francos-MacBook-Pro-2.local
The key's randomart image is:
+---[RSA 2048]----+
| o               |
|O .              |
|B* .             |
|=o+o   o         |
|o.=+E.. S        |
|o=+o+o . +       |
|o@*.  o o        |
|==X..  .         |
|O*oo             |
+----[SHA256]-----+
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
