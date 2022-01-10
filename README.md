## API Transferências

Neste projeto foram utilizadas as tecnologias:

- PHP 7.4
- Laravel 8
- MariaDB 10.7
- Docker

## Executando a aplicação

Para subir o ambiente criado no docker, foi utilizado um arquivo **Makefile**
para simplicar os comandos executados, sendo eles:

- `make init` Inicia os containers docker e a aplicação
- `make migrate` Executa as migrations
- `make seed` Executa os seeders para popular o banco de dados
- `make test` Executa a suite de testes **unitários** e de **integração**
- `make test-cov` Executa a suite de testes no modo coverage
- `make docs` Gera a documentação das rotas da API
- `make down` Encerra a execução da aplicação

Os comandos podem ser executados na raiz do projeto

## Acesso a API

Após subir o ambiente, a API estará disponível em:

> http://localhost:8021/api

## Documentação

Com o comando para gerar a documentação executado, ela estará disponível em:

> http://localhost:8021/api/docs

Para escrever a documentação, foi utilizado o **Swagger**.

## Correção do código

Para realizar a correção automática do codigo no projeto, executar o comando:

```
docker run -it --rm -v $(pwd):/project -w /project jakzal/phpqa:php7.4 phpmd app text cleancode,codesize,controversial,design,naming,unusedcode
```

## Realizando transferências

Ao executar o comando de seeders (`make seed`), o banco será populado com **três pessoas** e suas respectivas **
carteiras**
que podem ser utilizadas para realizar **transferências**.

* As pessoas de **id** 1 e 2 possuem carteiras com **id** 1 e 2 respectivamente.

* A terceira pessoa é **jurídica** (lojista), com **id** 3 e carteira de **id** 3.

Todas as pessoas possuem saldo de no mínimo 100.

### Obrigado =)