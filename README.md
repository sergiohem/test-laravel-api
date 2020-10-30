# Projeto exemplo - Desenvolvimento de API REST em PHP e Laravel

Este é um projeto desenvolvido em PHP utilizando o framework Laravel (versão 7) para a construção de uma API REST.

## Funcionamento

- O projeto tem como implementação um CRUD de Categorias, Fornecedores e Produtos, com métodos de criação, leitura, atualização e exclusão para serem consumidos usando API REST. Para tal consumo, há também os métodos direcionados para a autenticação de usuários da API;
- Para a segurança de requisições HTTP foi utilizado o [JWT](https://jwt.io/) (JSON Web Token);
- O banco de dados escolhido foi o MySQL.

## Aplicação

#### 1) Instalação do Composer

1.1) Baixe e instale o Composer (https://getcomposer.org/download/) em sua máquina, caso ainda não tenha instalado.

1.2) Na pasta raiz do projeto execute o seguinte comando:
    
    composer update
   
Este comando adicionará todos os pacotes do vendor presentes no projeto.

1.3) Referente ao pacote JWT (https://jwt-auth.readthedocs.io/en/docs/) instalado para este projeto, pode ser que se faça necessária a publicação de seu provider, caso após a primeira execução do projeto ocorra algum erro. Se for necessária, execute o seguinte comando:

    php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
    
#### 2) Configurando o arquivo .env

2.1) Utilize o arquivo .env.example presente na pasta raiz do projeto e o replique criando seu arquivo .env
2.2) Altere as configurações de banco de dados no seu arquivo .env criado, com as credenciais do seu banco.

    DB_CONNECTION=mysql
    DB_HOST={SUBSTITUA PELO HOST}
    DB_PORT={SUBSTITUA PELA PORTA}
    DB_DATABASE={SUBSTITUA PELO SCHEMA}
    DB_USERNAME={SUBSTITUA PELO USUÁRIO}
    DB_PASSWORD={SUBSTITUA PELA SENHA}
    
2.4) Execute o comando a seguir para gerar a chave secreta do JWT no arquivo .env:
        
    php artisan jwt:secret
    
#### 3) Configurando o banco de dados

3.1) O banco de dados utilizado para o projeto é o MySQL. Crie um schema no seu gerenciador de preferência para que ele receba as tabelas que serão geradas no próximo item (lembre-se de utilizar o mesmo nome que definiu no arquivo .env).
3.2) Como o schema criado, execute o seguinte comando para gerar as tabelas:

    php artisan migrate
    
3.3) Com as tabelas geradas, execute o seguinte comando para inserir registros pré-cadastrados pelos seeders:

    php artisan db:seed
    
#### 4) Executando o projeto

4.1) Para executar o projeto em seu ambiente local, execute o comando a seguir:

    php artisan serve
    
Por padrão, a API será executada em http://localhost:8000. Caso queira alterar a porta, execute o comando a seguir:

    php artisan serve --port {SUBSTITUA PELA PORTA DESEJADA}

#### 5) Rotas (endpoints) da API

5.1) Seguem abaixo as rotas da API, juntamente com seus tipos de requisição e funções no projeto:

| Rota | Tipo | Função |
| ---- | ------------------ | ------ |
| /api/register | POST | Realiza o cadastro do usuário |
| /api/login | POST | Realiza o login do usuário |
| /api/logout?token=INSERIR_TOKEN | GET | Realiza o logout do usuário através do token JWT enviado como parâmetro |
| /api/categorias | GET | Obtém todas as categorias cadastradas |
| /api/categorias/:id | GET | Obtém os dados de uma categoria através de seu ID |
| /api/categorias | POST | Realiza o cadastro de uma categoria |
| /api/categorias/:id | PUT | Realiza a atualização de uma categoria através de seu ID |
| /api/categorias/:id | DELETE | Realiza a exclusão de uma categoria através de seu ID |
| /api/fornecedores | GET | Obtém todos os fornecedores cadastrados |
| /api/fornecedores/:id | GET | Obtém os dados de um fornecedor através de seu ID |
| /api/fornecedores | POST | Realiza o cadastro de um fornecedor |
| /api/fornecedores/:id | PUT | Realiza a atualização de um fornecedor através de seu ID |
| /api/fornecedores/:id | DELETE | Realiza a exclusão de um fornecedor através de seu ID |
| /api/produtos | GET | Obtém todos os produtos cadastrados |
| /api/produtos/:id | GET | Obtém os dados de um produto através de seu ID |
| /api/produtos | POST | Realiza o cadastro de um produto |
| /api/produtos/:id | PUT | Realiza a atualização de um produto através de seu ID |
| /api/produtos/:id | DELETE | Realiza a exclusão de um produto através de seu ID |

Para realizar as chamadas de todas as requisições, com exceção da rota  /api/register, é necessário fornecer como autorização o Bearer Token, gerado pelo JWT no momento que a rota /api/login é chamada.

#### 6) Modelos de body JSON para requisições POST e PUT

6.1) /api/register - Cadastro de usuário (POST)

```json
{
    "name": "User Teste",
    "email": "user@teste.com",
    "password": "12345678"
}
```

6.2) /api/login - Login de usuário (POST)

```json
{
    "email": "user@teste.com",
    "password": "12345678"
}
```

6.3) /api/categorias - Cadastro de categorias (POST)

```json
{
    "nome": "Categoria Teste"
}
```

6.4) /api/categorias/:id - Atualização de categorias (PUT)

```json
{
    "nome": "Categoria Teste Update"
}
```

6.5) /api/fornecedores - Cadastro de fornecedores (POST)

```json
{
    "nomeFantasia": "Fornecedor Teste",
    "razaoSocial": "Fornecedor Teste LTDA",
    "cnpj": "31.541.491/0001-01",
    "cep": "36.010-040",
    "logradouro": "Avenida Teste",
    "numero": "1234",
    "bairro": "Bairro Teste",
    "cidade": "Juiz de Fora",
    "estado": "MG",
    "telefones": [
        "(32) 98888-8888",
        "(32) 99999-9999"
    ]
}
```

6.6) /api/fornecedores/:id - Atualização de fornecedores (PUT)

```json
{
    "nomeFantasia": "Fornecedor Teste Update",
    "razaoSocial": "Fornecedor Teste Update LTDA",
    "cnpj": "71.454.539/0001-69",
    "cep": "36.050-050",
    "logradouro": "Avenida Teste Update",
    "numero": "12345",
    "bairro": "Bairro Teste Update",
    "cidade": "Juiz de Fora",
    "estado": "MG",
    "telefones": [
        "(32) 93333-3333",
        "(32) 95555-5555"
    ]
}
```

6.7) /api/produtos - Cadastro de produtos (POST)

```json
{
    "nome": "Produto Teste",
    "descricao": "Este é um produto muito legal",
    "idCategoria": "1",
    "custosFornecedores": [
        {
            "idFornecedor": "1",
            "custo": "20.50"
        },
        {
            "idFornecedor": "2",
            "custo": "45.99"
        }
    ]
}
```

6.8) /api/produtos/:id - Atualização de produtos (PUT)

```json
{
    "nome": "Produto Teste Update",
    "descricao": "Este é um produto muito bacana",
    "idCategoria": "2",
    "custosFornecedores": [
        {
            "idFornecedor": "1",
            "custo": "20.50"
        }
    ]
}
```
