# Тег {{paginate}}
## Описание
Тег {{paginate}} используется для лимитирования итераторов, поддерживающих интерфейс lmbCollectionInterface и для передачи данных о количестве элементов в итераторе в pager-ы прямо в MACRO-шаблоне. Тег {{paginate}} ставится **до** pager-а и до тега {{list}}, который занимается выводом соответствующего итератора.

Тег {{paginate}}, по сути, может работать в двух режимах или как бы выволнять 2 задачи:

* может просто лимитировать итератор на основе значений своих атрибутов **limit** и **offset**,
* может связывать итератор с pager-ом, при этом значение атрибута **limit** будет работать аналогично атрибуту **items** [тега {{pager}}](./pager_tag.md).

## Область применения
В любом месте MACRO шаблона.

## Атрибуты

* **iterator** — название переменной, которая содержит итератор, поддерживающий интерфейс lmbCollectionInterface (пакет CORE).
* **pager** — идентификатор пейджера
* **limit** — количество элементов, которое необходимо вывести. Если **limit** не указан, но указан **pager**, то значение **limit** принимается равным значению атрибута items соответсвующего [тега pager](./pager_tag.md)
* **offset** — отступ от начала итератора, то есть количество элементов, которые нужно пропустить, прежде чем начать вывод. При использовании атрибута **pager** значение **offset** тег получает именно из pager-а автоматически.

## Содержимое
Нет.

## Пример использования
### Связь итератора с pager-ом

    {{paginate iterator='$#modules' pager='my_pager'}}
 
    {{pager id="my_pager" items="5"}}
    {{pager:list}}
      {{pager:current}}<b><a href="{$href}">{$number}</a></b>{{/pager:current}}
      {{pager:number}}<a href="{$href}">{$number}</a>{{/pager:number}}
      {{pager:separator}}-{{/pager:separator}}
    {{/pager:list}}
    {{/pager}}
 
    {{list using='$#modules'}}
    <table>
      {{list:item}}
      <tr>
       <td>{$item.title}</td>
       <td>{$item.desription}</td>
      </tr>
      {{/list:item}}
    </table>
    {{/list}}

### Простое ограничение размера итератора

    <h2>Лучшие фото рубрики </h2>
    <? $best_photos = ... ?>
 
    {{paginate iterator="$best_photos" limit="4" /}}
 
    {{list using="$best_photos"}}
    <ul id='best_photos_list'>
      {{list:item}}
       <li>{{apply template="photo_tpl" item="$item"/}}</li>
      {{/list:item}}
    </ul>
    {{/list}}
