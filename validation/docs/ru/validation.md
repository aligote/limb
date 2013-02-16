# Пакет VALIDATION
Limb жестко не навязывает Вам способ проверки данных, но предоставляет ряд средств, чтобы упростить этот процесс.

## Словарь терминов
Словарь терминов, которые мы будем использовать на данной странице:

* **Контейнер с данными**, который необходимо проверить (отвалидировать). Таким контейнером может быть Запрос (request), объект ActiveRecord, любой другой объект, который поддерживает интерфейс lmbSetInterface (то есть содержит метод get($field_name)).
* **Ошибка валидации** (validation error) — содержит сообщение об ошибке, список свойств, к которым применяется сообщение(опционально), и список неверных значений (опционально), которые находятся в указанных свойствах.
* **Список ошибок** (error_list) — содержит ошибки валидации
* **Правило валидации** (validation_rule) — проверяет правильность одного или нескольких свойств контейнера с данными.
* Набор правил, или **валидатор** (validator) — содержит набор правил и ссылку на список ошибок. Применяет последовательно правила валидации к указанному контейнеру данных.

## Общая схема валидации
Кратко процесс валидации данных можно описать следующим образом:

1. Клиент (код) создает пустой или какой-то конкретный валидатор, который содержит набор правил.
2. Клиент также создает список ошибок (или же валидатор содержит список ошибок по-умолчанию) и передает его в валидатор
3. Нужный контейнер данных передается в валидатор, который последовательно передает этот контейнер данных и список ошибок в каждое правило.
4. Если правило валидации не срабатывает, то есть свойство или несколько свойств контейнера данных не удовлетворяют условию правила, оно добавляет ошибку в список ошибок, указывая текст ошибки, список ошибочных свойств и неверные значения, если необходимо.
5. Если после работы валидатора список ошибок не пуст — валидация считается неудавшейся

На практике некоторые шаги могут быть пропушены. Например, вы можете отказаться от применение валидаторов с правилами и проверять данные, добавлять ошибки в список ошибок прямо в коиентском коде.

## Валидатор. Класс lmbValidator. Создание своих валидаторов
Валидатор представлен в Limb классом lmbValidator.

Для добавления правил валидации lmbValidator содержит метод **addRule($rule)**:

    lmb_require('limb/validation/src/lmbValidator.class.php');
    $validator = new lmbValidator();
    lmb_require('limb/validation/src/rule/lmbMatchRule.class.php');
    $validator->addRule(new lmbMatchRule('password', 'repeat_password'));

Правило обязательности поля lmbRequiredRule используется очень часто, поэтому для него есть специальный метод **addRequiredRule($field_name)**:

    $validator->addRequiredRule('login');
    $validator->addRequiredRule('password');

Вы также можете создать свои классы валидаторов, если один и тот же набор правил используется в проекте несколько раз в различных местах.

    lmb_require('limb/validation/src/lmbValidator.class.php');
    lmb_require('limb/validation/src/rule/lmbRequiredRule.class.php');
    lmb_require('limb/validation/src/rule/lmbSizeRangeRule.class.php');
 
    class MyValidator extends lmbValidator
    {
      function __construct()
      {
        $this->addRule(new lmbRequiredRule('name'));
        $this->addRule(new lmbSizeRangeRule('name', 5, 20));
      }
    }

Если же добавление правил связано с какими-либо условиями, которые проявляются только на этапе валидации, тогда добавление правил можно осуществлять в методе **validate($datasource)**, который запускает процесс валидации контейнера данных:

    lmb_require('lmbValidator.class.php');
    lmb_require('limb/validation/src/rule/lmbRequiredRule.class.php');
    lmb_require('limb/validation/src/rule/lmbSizeRangeRule.class.php');
 
    class MyValidator extends lmbValidator
    {
      function validate($datasource)
      {
        $this->addRule(new lmbRequiredRule('name'));
        $this->addRule(new lmbSizeRangeRule('name', 5, 20));
 
        return parent :: validate($datasource);
      }
    }

В качестве валидируемого контейнера данных ($datasource) в общем случае подойдет любой объект, который поддерживает метод **get($field_name)** (интерфейс **lmbSetInterface**).

Обычно новые валидаторы хранятся в папке /src/validation пакетов (приложения на базе Limb обычно также считаются пакетами), а новые правила валидации - в /src/validation/. Обратите внимание, что в пакете валидации правила валидации хранятся в папке /src/rule. Имейте это ввиду при использовании lmb_require.

lmbValidator в качестве параметра конструктора может получать объект списка ошибок валидации. Таким образом можно в один список ошибок собрать ошибки от нескольких валидаторов. Если же список ошибок в конструктор валидатора не передан, то используется новый объект класса lmbErrorList.

Список ошибок можно также передать в валидатор при помощи метода **setErrorList($error_list)**.

## Список ошибок. Классы lmbErrorList
Пакет VALIDATION содержит реализацию списка ошибок в виде класса **lmbErrorList**.

Класс lmbErrorList отнаследован от класса lmbCollection, то есть это — полноценный итератор.

Класс lmbErrorList содержит следующие методы:

* **addError($message, $fields = array(), $values = array())**
* **isValid()** — возвращает true, если нет ошибок валидации, иначе false. Вместо isValid() можно примерять более базовый метод isEmpty(), что эквивалетно.

Если правило валидации обнаруживает ошибку в контейнере данных, тогда оно должно известить список ошибок посредством вызова метода addError($message, $field_list = array(), $values = array()). Поясним параметры этого метода:

* $message — cообщение об ошибке. Обычно сообщение сразу же локализуется (переводится) при помощи глобальной функции tr().
* $field_list — это или список пар «алиас ⇒ имя поля», которые имели ошибки или же просто список полей, содержащих ошибки, которые не подошли под какое-либо правило.
* $values — список некоторых пар «алиас ⇒ значение», которые используются в сообщении об ошибке.

Например, вызов addError может выглядеть следующим образом:

    $error_list->addError($message = tr('/validation', '{Field} must be greater than {min} characters.'),
                          $fields = array('Field' => 'name'),
                          $values = array('min' => 5));

При выводе ошибки валидации спец. места сообщения, например, {Field} и {min} будут заменены на соответствующие значения (см. ниже). При этом значение параметра $fields будут также обработаны при помощи словаря полей для получания человеко-понятных имен полей. В результате, в конечном итоге мы пожем получить следующее сообщение об ошибке: «Name must be greater than 5 characters».

## Правило валидации. Интерфейс lmbValidationRule
**lmbValidationRule** — это интерфейс правила валидации пакета валидации данных. Интерфейс правила валидации состоит всего из одного метода: **validate($dataspace, $error_list)**. То есть правило валидации получает просто контейнер с данными, которые нужно проверить и список ошибок, куда будут добавляться ошибки, в случае их наличия.

## Стандартные правила валидации
Правила валидации находятся в папке **limb/validation/src/rule/**

Имя класса | Назначение
-----------|-----------
lmbSingleFieldRule | Базовый (абстрактный) класс для правил валидации, относящихся только к одному полю.
lmbRequiredRule	| Проверяет, что поле обязательно присутствует в контейнере данных, который нужно проверить
lmbSizeRangeRule | Проверяет, что поле не больше определенной длины или находится в определенных рамках
lmbMatchRule | Проверяет, что два поля имеют одинаковые значения.
lmbPatternRule | Проверяет, что значение поле подходит под определенное регулярное выражение
lmbSimpleUrlRule | Удостоверяется, что значение поля является URL-адресом (достаточно примитивная проверка)
lmbDomainRule | Удостоверяется, что значение поля является именем домена
lmbInvalidValueRule	| Удостоверяется, что поле имеет отличное от указанного в конструкторе правила значения
lmbUniqueTableFieldRule	| Проверяет, что значение поля является уникальным для поля определенной таблицы в базе данных
lmbFileUploadRequiredRule	| Удостоверяется, что файл был закачан
lmbFileUploadMaxSizeRule | Удостоверяется, что файл был закачан меньшего, чем указано размера
lmbFileUploadMimeTypeRule | Удостоверяется, что был закачан файл только определенного типа

[Все правила валидации](./rules.md)

## Валидация в веб-приложениях
Подробнее об особенностях валидации данных в веб-приложениях вы можете прочитать в [документации к пакету WEB_APP](../../../web_app/docs/ru/web_app/validation.md).