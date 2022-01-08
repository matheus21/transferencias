## API Transferências

Neste projeto foram utilizadas as tecnologias:

- PHP 7.4
- Laravel 8
- MariaDB 10.7
- Docker

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