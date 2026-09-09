## Pesquisa PDO

No desenvolvimento de aplicações baseadas na linguagem PHP, surgiu a necessidade de juntar o acesso de diversas extensões de banco de dados presentes na linguagem, com isso surgiu o PHP Data Object (PDO), que realiza também a abstração do banco de dados.
A sua vantagem está no objetivo de fornecer uma biblioteca limpa e consistente, para deixar unificadas as características das extensões que acessam os bancos de dados. Porém, o PDO possui também algumas desvantagens, por exemplo, não efetua a leitura e tradução das instruções SQL, é apenas realizada uma fusão dos métodos mandados para as extensões respectivas.
O PDO (PHP Data Objects) define uma interface de conexão a banco de dados leve e consistente para PHP. Há a possibilidade de utilização de diversos drivers de conexão que implementam a interface do PDO para vários tipos de bancos de dados.
Como o PDO representa uma camada de abstração de acesso aos dados, as mesmas funções utilizadas para manipular dados ou recuperar informações do banco serão as mesmas, independentemente do banco de dados que esteja sendo usado.
Características PDO
Flexibilidade – Como o PDO carrega o driver específico do banco de dados em tempo de execução, não é preciso reconfigurar o PHP sempre que um banco de dados diferente for usado.
Desempenho – O PDO está escrito em C e compilado no PHP, o que lhe garante um aumento considerável no desempenho em relação a soluções escritas em PHP.
Consistência de código – No PDO não existe a inconsistência de código, pois é oferecida apenas uma interface unificada que é está disponível para qualquer banco de dados.
Características de orientação de objetos – Possui recursos de orientação de objetos, o que resulta em uma comunicação mais poderosa e eficiente com banco de dados.
Instalação do PDO
O PDO vem junto com a versão 5.1 do PHP ou mais recente, por isso quem possuir essa versão terá apenas que efetuar uma configuração, adicionando a linha abaixo no arquivo.
O PDO (PHP Data Objects) é uma camada de abstração genérica para vários bancos de dados, enquanto o MySQLi (MySQL Improved) é uma extensão desenvolvida especificamente para se conectar e gerenciar bancos de dados MySQL.
Vantagens do PDO
Suporta vários bancos de dados: Você pode usar o mesmo código para conectar ao MySQL, PostgreSQL, SQLite, Oracle ou SQL Server apenas mudando a configuração. 
Segurança contra SQL Injection: O uso de consultas preparadas (prepared statements) separa o código SQL dos dados enviados pelo usuário, bloqueando códigos maliciosos.
Orientação a objetos: O PDO utiliza uma API limpa baseada em objetos, o que organiza melhor o código da aplicação.
Manutenção simples: Se precisar trocar o banco de dados do sistema no futuro, você altera poucos trechos do código de conexão.
Desvantagens do PDO
Não traduz comandos SQL: O PDO não corrige a linguagem SQL. Se você mudar de banco, comandos específicos de um SGBD podem não funcionar no outro. 
Apenas orientado a objetos: O PDO não oferece uma interface procedural. Quem está acostumado com códigos antigos baseados em funções puras precisa aprender orientação a objetos. 
Curva de aprendizado: Para desenvolvedores iniciantes, entender a estrutura de conexão e os parâmetros pode ser mais complexo no começo.
O que são Prepared Statements?
Prepared Statements (declarações preparadas) são uma forma segura de executar comandos SQL em que o código SQL e os dados fornecidos pelo usuário são tratados separadamente.
Em vez de montar uma consulta concatenando valores diretamente:
SELECT * FROM usuarios WHERE email = 'usuario@email.com';
usamos parâmetros:
SELECT * FROM usuarios WHERE email = ?;
E depois enviamos o valor do parâmetro separadamente:
email = "usuario@email.com"
Situações em que o PDO é uma boa escolha


Aplicações PHP que acessam bancos de dados: permite trabalhar com MySQL, PostgreSQL, SQLite, SQL Server e outros bancos por meio de uma interface semelhante.


Quando é necessário usar Prepared Statements: o PDO facilita o uso de consultas parametrizadas, ajudando a proteger a aplicação contra SQL Injection.


Projetos que podem mudar de banco de dados: como o código de acesso ao banco fica mais padronizado, a migração entre determinados SGBDs pode ser mais simples.


Aplicações que precisam tratar erros adequadamente: o PDO oferece um sistema de exceções (PDOException) que facilita identificar e tratar problemas de conexão ou execução de consultas.


Projetos que utilizam transações: o PDO possui suporte a beginTransaction(), commit() e rollBack(), úteis quando várias operações precisam ser executadas como uma única unidade.


Sistemas de pequeno a grande porte: desde aplicações simples até APIs e sistemas web mais complexos, o PDO pode ser utilizado.


Por que são importantes?
Protegem contra SQL Injection: como os valores são tratados como dados, um usuário mal-intencionado não consegue facilmente transformar uma entrada em parte do comando SQL.


Aumentam a segurança: evitam a concatenação direta de dados externos dentro das consultas.


Facilitam o código: a mesma consulta pode ser reutilizada com diferentes valores.


Podem melhorar o desempenho: em alguns bancos de dados, a consulta preparada pode ser compilada/planejada uma vez e reutilizada.





## Referências
https://www.treinaweb.com.br/blog/o-que-e-pdo-no-php
https://www.devmedia.com.br/php-pdo-como-se-conectar-ao-banco-de-dados/37211
https://www.youtube.com/watch?v=yUDLQI8CAog&t=78s
https://blog.grancursosonline.com.br/php-pdo-vs-mysqli/
https://www.quora.com/What-are-the-pros-and-cons-of-Mysqli-and-PDO
https://alefesouza.github.io/php-tutorial/database/pdo.html
https://www.devmedia.com.br/introducao-ao-php-data-objects-pdo/25318
https://www.guj.com.br/t/o-que-e-preparedstatement-e-para-que-serve/86774/
https://www.reddit.com/r/PHP/comments/1an3emv/why_is_the_performance_benefit_of_prepared/?tl=pt-pt
