# Pesquisa sobre APIs — Projeto Ferroramas

## 1. O que é uma API e qual é sua finalidade?

API significa *Application Programming Interface* (Interface de Programação de Aplicações). É uma forma de um programa oferecer funções ou informações para outro programa, seguindo regras definidas. Sua finalidade é facilitar a integração, sem que um sistema precise conhecer todos os detalhes internos do outro.

No Ferroramas, por exemplo, uma API poderia permitir que uma aplicação consultasse os trens cadastrados sem acessar diretamente o banco de dados.

## 2. Como funciona a comunicação entre sistemas?

Geralmente, um cliente (como um site ou aplicativo) envia uma requisição HTTP para um servidor. O servidor recebe o pedido, verifica os dados e as permissões, consulta o banco de dados quando necessário e devolve uma resposta. Essa resposta pode conter dados em JSON e um código de status HTTP.

Exemplo: a tela de trens solicita a lista de trens; o backend consulta o banco e retorna os registros para a tela mostrar.

## 3. O que são APIs REST?

REST é um estilo de arquitetura usado para organizar a comunicação entre sistemas. Em APIs REST, os recursos são identificados por endereços (URLs), e métodos HTTP indicam o que deve ser feito. Um recurso pode ser um usuário, trem ou rota. Uma característica importante é que cada requisição deve trazer as informações necessárias para ser processada, sem depender de um estado de conversa guardado entre requisições.

Exemplos de endereços: `/api/trens` para a coleção de trens e `/api/trens/5` para um trem específico.

## 4. Métodos HTTP

| Método | Para que serve | Exemplo no Ferroramas |
|---|---|---|
| GET | Consultar dados | `GET /api/trens` |
| POST | Enviar dados, normalmente para criar um registro | `POST /api/trens` |
| PUT | Substituir os dados de um recurso | `PUT /api/trens/5` |
| PATCH | Atualizar apenas alguns campos | `PATCH /api/trens/5` |
| DELETE | Excluir um recurso | `DELETE /api/trens/5` |

Por exemplo, para alterar apenas o nome de um trem, o método PATCH pode ser mais adequado que PUT.

## 5. Requisições e respostas HTTP

A requisição é o pedido enviado pelo cliente. Ela inclui um método, um endereço, cabeçalhos e, quando necessário, um corpo com dados. A resposta é o retorno do servidor, com um código de status, cabeçalhos e possivelmente um corpo.

Alguns códigos comuns são: `200 OK` (sucesso), `201 Created` (criado), `400 Bad Request` (pedido inválido), `401 Unauthorized` (falta autenticação), `403 Forbidden` (sem permissão), `404 Not Found` (não encontrado) e `500 Internal Server Error` (erro interno).

## 6. O que são endpoints?

Endpoints são os endereços pelos quais acessamos funcionalidades ou recursos de uma API. Por exemplo, `/api/usuarios`, `/api/trens`, `/api/rotas` e `/api/sensores` poderiam ser endpoints do Ferroramas. Esses endereços são exemplos de como o projeto poderia organizar uma API, não uma afirmação de que já foram implementados.

## 7. Troca de dados em JSON

JSON (*JavaScript Object Notation*) é um formato de texto usado para organizar e trocar informações. Ele utiliza pares de chave e valor e pode representar textos, números, valores booleanos, listas e objetos.

Exemplo de um trem:

```json
{
  "id": 5,
  "nome": "Trem Regional",
  "status": "ativo",
  "responsavel_id": 2
}
```

O frontend pode ler esses campos e mostrar as informações na tela. O backend também pode receber JSON para cadastrar ou atualizar registros.

## 8. Diferença entre API e sistema web tradicional

Um sistema web tradicional costuma devolver páginas HTML prontas para o navegador. Já uma API geralmente devolve dados, como JSON, para serem utilizados por outros programas. As duas formas podem existir no mesmo projeto: o site apresenta a interface, enquanto a API fornece os dados. Nem toda API usa HTTP ou JSON, mas isso é comum nas APIs web.

## 9. Como uma aplicação PHP pode consumir uma API?

Em PHP, podemos usar a biblioteca cURL para fazer uma requisição HTTP. O exemplo abaixo consulta uma API de demonstração pública e transforma a resposta JSON em um array PHP:

```php
<?php
$url = 'https://jsonplaceholder.typicode.com/users/1';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$resposta = curl_exec($ch);
$codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($resposta === false) {
    echo 'Erro ao consultar a API: ' . curl_error($ch);
} elseif ($codigo !== 200) {
    echo 'A API retornou o status: ' . $codigo;
} else {
    $usuario = json_decode($resposta, true);
    echo $usuario['name'] ?? 'Nome não encontrado';
}

curl_close($ch);
```

Esse exemplo depende de conexão com a internet e da extensão cURL habilitada no PHP. Em um projeto real, também é importante validar o conteúdo retornado e tratar erros de JSON.

## 10. Como uma API disponibiliza informações?

O backend pode criar um endpoint que consulta o banco de dados e devolve um resultado em JSON. Um exemplo simples em PHP, com dados fictícios, seria:

```php
<?php
header('Content-Type: application/json; charset=utf-8');

$trens = [
    ['id' => 1, 'nome' => 'Trem Azul'],
    ['id' => 2, 'nome' => 'Trem Verde']
];

http_response_code(200);
echo json_encode($trens);
```

Em uma aplicação real, esses dados poderiam vir de uma consulta ao MySQL, com verificação das permissões antes de devolver informações protegidas.

## 11. Autenticação, autorização e segurança

Autenticação é confirmar quem está acessando o sistema, por exemplo, por login e senha. Autorização é verificar o que essa pessoa pode fazer. Um usuário comum pode consultar informações, enquanto um administrador pode ter permissão para cadastrar ou excluir registros.

É importante usar HTTPS, validar dados recebidos, proteger tokens e sessões, limitar o acesso aos endpoints e nunca armazenar senhas em texto puro. Em PHP, funções como `password_hash()` e `password_verify()` ajudam a armazenar e conferir senhas com segurança. A verificação de permissões deve acontecer no backend, porque esconder um botão na tela não impede alguém de tentar acessar diretamente uma URL.

## 12. Exemplos práticos de requisições e respostas

Os exemplos a seguir são ilustrativos e representam uma possível API do Ferroramas.

### Consultar trens com GET

Requisição:

```http
GET /api/trens HTTP/1.1
Host: exemplo.com
Accept: application/json
```

Resposta:

```http
HTTP/1.1 200 OK
Content-Type: application/json

[{"id":1,"nome":"Trem Azul"},{"id":2,"nome":"Trem Verde"}]
```

### Cadastrar trem com POST

Requisição:

```http
POST /api/trens HTTP/1.1
Host: exemplo.com
Content-Type: application/json

{"nome":"Trem Vermelho","responsavel_id":2}
```

Resposta:

```http
HTTP/1.1 201 Created
Content-Type: application/json

{"id":3,"nome":"Trem Vermelho","responsavel_id":2}
```

### Substituir dados com PUT

```http
PUT /api/trens/3 HTTP/1.1
Host: exemplo.com
Content-Type: application/json

{"nome":"Trem Expresso","responsavel_id":2}
```

### Alterar um campo com PATCH

```http
PATCH /api/trens/3 HTTP/1.1
Host: exemplo.com
Content-Type: application/json

{"nome":"Trem Expresso Atualizado"}
```

### Excluir com DELETE

```http
DELETE /api/trens/3 HTTP/1.1
Host: exemplo.com
```

Uma exclusão bem-sucedida pode retornar `204 No Content`, sem corpo na resposta. Todos esses exemplos pressupõem que o usuário tenha as permissões necessárias.

## 13. Exemplos de APIs em empresas

APIs são usadas em lojas virtuais para consultar fretes e pagamentos, em aplicativos de transporte para trabalhar com mapas e localização, em empresas para integrar sistemas de estoque e vendas e em serviços de atendimento para conectar diferentes plataformas. No Ferroramas, uma API poderia permitir que outro sistema consultasse o estado dos trens, rotas e sensores, respeitando as permissões de acesso.

## Conclusão

Com a pesquisa, entendemos que as APIs ajudam diferentes sistemas a trocar informações de maneira organizada. Os métodos HTTP indicam as operações, os endpoints mostram onde acessar os recursos e o JSON facilita a troca de dados. Também vimos que a segurança é essencial, principalmente quando existem usuários com permissões diferentes. Esses conceitos podem ser úteis para futuras integrações do Ferroramas.

## Fontes consultadas

- MDN Web Docs. **Métodos de requisição HTTP**. https://developer.mozilla.org/pt-BR/docs/Web/HTTP/Reference/Methods
- MDN Web Docs. **Visão geral do HTTP**. https://developer.mozilla.org/pt-BR/docs/Web/HTTP/Overview
- MDN Web Docs. **JSON**. https://developer.mozilla.org/pt-BR/docs/Learn_web_development/Core/Scripting/JSON
- PHP Manual. **Exemplos básicos de cURL**. https://www.php.net/manual/en/curl.examples-basic.php
- PHP Manual. **json_decode**. https://www.php.net/manual/pt_BR/function.json-decode.php
- PHP Manual. **password_hash**. https://www.php.net/manual/pt_BR/function.password-hash.php
- OWASP. **REST Security Cheat Sheet**. https://cheatsheetseries.owasp.org/cheatsheets/REST_Security_Cheat_Sheet.html
