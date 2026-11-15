# Manual de utilização do Pampa-Serra

O Pampa-Serra é o sistema da SA Ferrorama para consultar trens, rotas, sensores e ocorrências ferroviárias. Este manual descreve a versão de referência `cfe8186`.

## 1. Acessar o sistema

1. Abra no navegador o endereço informado pela equipe. Em uma instalação XAMPP com o projeto diretamente em `htdocs/projeto-pampa-serra`, o endereço é `http://localhost/projeto-pampa-serra/`. Se a pasta estiver dentro de outro diretório, inclua esse diretório no endereço.
2. Na página inicial, clique em **Acessar sistema**.
3. Informe o e-mail e a senha da conta de demonstração e clique em **Entrar**.
4. Para encerrar a sessão, volte à **Visão geral** e clique em **Sair**.

| Perfil | Acesso |
| --- | --- |
| Usuário comum | Consulta a Visão geral, trens, rotas, sensores e relatórios. |
| Administrador | Tem acesso às consultas e pode gerenciar usuários, cadastrar e editar trens, rotas e sensores e simular ocorrências nas telas que oferecem essa função. |

## 2. Navegar pela Visão geral

- Use o menu lateral para abrir **Rotas**, **Sensores**, **Trens** e **Relatórios**. Clique em **Visão geral** para voltar ao painel.
- Use a busca do painel para encontrar um trem pelo código ou modelo, um sensor pelo nome ou uma rota pelo código ou pela localidade. Os resultados são agrupados por módulo. Clique em **Limpar busca** para voltar ao painel.
- Em **Situação das rotas**, confira os totais e a lista de rotas ativas recentes. Abra uma rota pelo código para ver seus detalhes. **Ver todas as rotas** abre a lista completa.
- Em **Relatórios**, consulte os totais de registros e os indicadores de ocorrências, velocidade e situação operacional. Use os botões de exportação da página para baixar os dados em CSV.

## 3. Usuários

A gestão de usuários fica disponível no perfil administrador.

- **Cadastrar.** Na **Visão geral**, selecione **Cadastrar**. Preencha nome, e-mail, telefone e senha conforme o formulário e escolha o tipo de usuário. Clique em **Cadastrar Usuário**.
- **Consultar.** Em **Cadastrar Usuário**, selecione **Ver Usuários**. Use a busca da lista para localizar a conta e confira o perfil e as opções disponíveis na linha correspondente.

## 4. Rotas

- **Consultar.** Abra **Rotas**, busque pelo código, pela origem ou pelo destino e clique em **Ver** na linha desejada. Confira origem, destino, parada e situação. As faixas de velocidade e consumo representam os valores configurados para a rota.
- **Cadastrar.** No perfil administrador, escolha **+ Cadastrar Rota**. Preencha código, origem, destino e os campos operacionais solicitados. Defina se a rota ficará ativa e clique em **Cadastrar Rota**. Uma rota ativa pode ser selecionada no cadastro de um trem.

## 5. Trens

- **Consultar.** Abra **Trens**, localize o registro e clique em **Ver** para consultar os detalhes. Se houver uma rota vinculada, o código dela na lista abre a tela da rota. A coluna de status mostra a situação registrada para o trem.
- **Cadastrar.** No perfil administrador, selecione **+ Cadastrar trem**. Informe código e modelo, escolha o status e selecione o usuário responsável. Em **Rota ativa**, selecione uma rota ou deixe **Sem rota**. Clique em **Cadastrar Trem**.
- **Interpretar o status.** **Normal** indica a condição regular registrada. **Alerta** sinaliza uma condição que requer atenção. **Falha** identifica uma condição registrada como falha.

## 6. Sensores

- **Consultar.** Abra **Sensores** e use a busca para localizar um sensor. Clique em **Ver** para consultar as informações do cadastro, como nome, localização e tipo de dado.
- **Cadastrar.** No perfil administrador, use o botão de criação na tela **Sensores**. Informe o nome, o tipo de dado, a localização e a descrição, conforme o formulário. Clique em **Cadastrar sensor**.
- **Simular uma ocorrência.** No ambiente de demonstração, abra o detalhe de um sensor com perfil administrador e use **Simular incidente**, se a ação estiver disponível. Confira o resultado em **Relatórios**, na seção **Incidentes e ocorrências**.

## 7. Roteiro rápido de demonstração

1. Entre como administrador. Confirme que há um usuário responsável e uma rota ativa de demonstração.
2. Cadastre um trem fictício com esse responsável e vincule-o à rota ativa. Volte à lista de trens e abra a rota pelo código.
3. Consulte um sensor e confira as informações em **Relatórios**. Encerre a sessão.
4. Entre como usuário comum, abra o mesmo trem e a rota vinculada e confira as opções disponíveis para o perfil. Clique em **Sair**.

Se aparecer **Nenhum trem encontrado**, limpe os filtros e procure novamente. Se a lista continuar vazia, peça a um administrador para cadastrar um trem de demonstração. Se o navegador não conseguir abrir o endereço, confirme com a pessoa responsável pela estação se o servidor e o banco estão em execução.
