## trainer_profile
Ideia: Separar specialization e specification para novas tabelas

Secalhar é complexadidade a mais para uma base de dados "mais simples"

A vantagem atual é simplicidade, e só se vai usar no perfil do treinador, ou seja é trql ter tipo string

## tipo_de_aula
type pode ter mais aulas. Secalhar devia dar p adicionar

## class vs class_session — onde ficam os atributos?

### Discussão
A separação entre `class` (template) e `class_session` (ocorrência agendada) levanta a questão de onde colocar atributos como `difficulty`, `duration_minutes`, `capacity` e `trainer_id`.

### Decisão atual
- `class` tem: `name`, `type_id`, `description`, `duration_minutes`, `intensity`, `trainer_id`
- `class_session` tem: `datetime`, `room`, `capacity`

**Racional:**
- `duration_minutes` e `intensity` ficam na `class` porque não variam entre sessões da mesma aula
- `capacity` fica na `class_session` porque depende da sala
- `trainer_id` fica na `class` porque a aula pertence a um trainer — se houver substituto numa sessão, não é possível registar isso

### Alternativa não implementada: difficulty/intensity na class_session
Se o ginásio quiser oferecer a mesma aula em níveis diferentes (ex: "Yoga Beginner" às 9h e "Yoga Advanced" às 11h), na arquitetura atual seriam duas `class` separadas. Com intensity na `class_session`, seria uma `class` com duas sessões de intensidades diferentes.

**Prós de manter na `class`:** simplicidade, sem repetição de dados, mais realista (ginásios listam "Yoga Beginner" e "Yoga Advanced" como aulas distintas)

**Prós de mover para `class_session`:** mais flexível, menos classes na tabela

---

## Reviews em class vs class_session

### Discussão
Reviews ligadas à `class_session` permitem avaliar uma sessão específica (ex: "esta segunda foi má porque o professor estava doente"). Reviews ligadas à `class` são mais intuitivas para o utilizador — "é esta aula boa?" e não "foi esta sessão específica boa?".

### Decisão atual
Reviews referem `class_id` — um membro dá uma review à aula, não à sessão. `UNIQUE(member_id, class_id)` garante uma review por aula por membro.

---