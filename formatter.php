<?php

$sources = yaml_parse_file($argv[1])['sources'];

// echo var_dump($sources);

foreach ($sources as $i => $source) {
    if (isset($source['authors'])) {
        $authors = $source['authors'];
        if (gettype($authors) === 'string') {
            $authors = explode(',', $authors);
            foreach ($authors as &$author) {
                $author = trim($author);
            }
        }
        $source['authors'] = parseFullnames($authors);
    }

    $s = ($i + 1) . '. ';

    switch ($source['type']) {
        case 'Книга':
            $s .= formatBook($source);
            break;
        case 'Статья из журнала':
            $s .= formatJournalArticle($source);
            break;
        case 'Статья из сборника':
            $s .= formatDigestArticle($source);
            break;
        case 'Ссылка':
            $s .= formatLink($source);
            break;
    }

    echo $s . "\n";
}

function parseFullnames(array $fullnames): array
{
    $newFullnames = [];

    foreach ($fullnames as $fullname) {
        $parts = explode(' ', $fullname);
        $lastName = $parts[0];

        if (count($parts) === 2) {
            $initials = $parts[1];
        } else if (count($parts) === 3) {
            $initials = $parts[1] . $parts[2];
        }

        $newFullnames[] = ['last_name' => $lastName, 'initials' => $initials];
    }
    return $newFullnames;
}

function formatBook(array $source): string
{
    $s = '';

    $authors = $source['authors'];
    $authorsCount = count($authors);
    // описание начинается с фамилии и инициалов первого автора
    if ($authorsCount <= 3) {
        $s .= $authors[0]['last_name'] . ', ' . $authors[0]['initials'] . ' ';
    } else {
        // todo
    }

    $s .= $source['title'] . ' [Текст] / ';

    // За косой чертой указываются все авторы.
    if ($authorsCount <= 4) {
        $authorStrings = [];
        foreach ($authors as $author) {
            $authorStrings[] = $author['initials'] . ' ' . $author['last_name'];
        }

        $s .= implode(', ', $authorStrings);
    } else { // За косой чертой указываются 3 автора и др.
        // todo
    }

    if (isset($source['eng_translaters'])) {
        $s .= '; пер. с анг. ' . $source['eng_translaters'];
    }

    $s .= '. – ';
    $s .= $source['edition']['city'] . ': ' .
        $source['edition']['name'] . ', ' .
        $source['edition']['year'] . '. – ' .
        $source['pages_count'] . ' с.';

    return $s;
}

function formatJournalArticle(array $source): string
{
    // 1. Тарасова, Н.Г. Смена парадигм в развитии теории и практики градостроительства
    // [Текст] / Н.Г. Тарасова // Архитектура и строительство России. – 2007. - № 4. – С. 2-7.

    // 2. Казаков, Н.А. Запоздалое признание [Текст] / Н.А. Казаков // На боевом посту. –
// 2000. - № 9. – С. 64-67; № 10. – С. 58-71.
    $s = '';

    $authors = $source['authors'];

    $authorStrings = [];
    foreach ($authors as $author) {
        $authorStrings[] = $author['last_name'] . ', ' . $author['initials'];
    }
    $s .= implode(', ', $authorStrings) . ' ';

    $s .= $source['title'] . ' [Текст] / ';

    $authorStrings = [];
    foreach ($authors as $author) {
        $authorStrings[] = $author['initials'] . ' ' . $author['last_name'];
    }
    $s .= implode(', ', $authorStrings);

    $s .= ' // ' . $source['journal']['name'] . '. – ' .
        $source['journal']['year'] . '. - ';

    $editionStrings = [];
    foreach ($source['journal']['editions'] as $edition) {
        $editionStrings[] = '№ ' . $edition['number'] . '. – С. ' . $edition['pages'];
    }

    $s .= implode('; ', $editionStrings) . '.';

    return $s;
}

function formatDigestArticle(array $source): string
{
    // 1. Думова, И.И. Инвестиции в человеческий капитал [Текст] / И.И. Думова, М.В. Ко-
// лесникова // Современные аспекты регионального развития: сб. статей. – Иркутск, 2001. – С.
// 47-49.

    $s = '';

    $authors = $source['authors'];

    $authorStrings = [];
    foreach ($authors as $author) {
        $authorStrings[] = $author['last_name'] . ', ' . $author['initials'];
    }
    $s .= implode(', ', $authorStrings) . ' ';

    $s .= $source['title'] . ' [Текст] / ';

    $authorStrings = [];
    foreach ($authors as $author) {
        $authorStrings[] = $author['initials'] . ' ' . $author['last_name'];
    }
    $s .= implode(', ', $authorStrings);


    $s .= ' // ' . $source['digest']['name'] . ': сб. статей. – ' .
        $source['digest']['city'] . ', ' .
        $source['digest']['year'] . '. – С. ' .
        $source['digest']['pages'] . '.';

    return $s;
}

function formatLink(array $source): string
{
    // СТО
    // Шпринц, Лев. Книга художника: от миллионных тиражей – к единичным экземплярам [Электронный ресурс] / Л. Шпринц. – Электрон. текстовые дан. – Москва: [б.и.], 2000. – Режим доступа: http://atbook.km.ru/news/000525.html, свободный.

    // Дипломы
    // 10.	Поиск в глубину [Электронный ресурс]. – Электрон. статья – Режим доступа: http://e-maxx.ru/algo/dfs, свободный – Дата доступа: 02.04.2022.
    // 13.	ECMA-262 [Электронный ресурс]. – Стандарт JavaScript – Режим доступа: https://www.ecma-international.org/publications-and-standards/standards/ecma-262/, свободный – Дата доступа: 20.05.2022.

    return $source['name'] . ' [Электронный ресурс]. – ' .
        $source['description'] . ' – Режим доступа: ' .
        $source['url'] . ', свободный. – Дата доступа: ' .
        $source['access_date'];
}