# moodle-local_aise (Accent Insensitive Search Enabler)

Moodle core code reports, that [accent insensitive search is not possible with PostgreSQL](https://github.com/moodle/moodle/blob/a891866cbd1dc1e106bcf1fb808ea144b8fb9cf3/lib/dml/pgsql_native_moodle_database.php#L1480). 

This is only partly true, as there a various approaches how to achieve this goal. However, all alternatives are dependend
on the actual database configuration. This plugin relies on the PostgreSQL-extension `unaccent` and modifies SQL-statements
accordingly.

> There is no need for Moodle administrators to install this plugin separately. It will only do its job, if it is used by
> developers within their code. If developers use this plugin, they need to link it as dependency in their version.php.
> Hence, this plugin will be installed automatically if needed!

## What this plugin does

Lets say in our user-Table we have three guys called "Jose", each with different spelling.

| id | firstname |
| -- |-----------|
|  1 | Jose      |
|  2 | José      |
|  3 | JosÉ      |

When developers use the Moodle data manipulation API correctly, they may do a sql like query as follows:

```php
$searchname = $DB->sql_like_escape('jose');
$sqllike = $DB->sql_like('firstname', '?');
$sqlparams = [ $searchname ];
$sql = "SELECT *
          FROM {user}
          WHERE {$sqllike}";
$records = $DB->get_records_sql($sql, $sqlparams);          
```

As you may suggest, this search should be case insensitive **and** accent insensitive, because the function `php sql_like()`
provides the two parameters `$casesensitive` and `$accentsensitive` which default to false. The resulting sql-query is in
MySQL

```sql
SELECT * FROM mdl_user WHERE firstname LIKE 'jose' ESCAPE '\'
```

In PostgreSQL it would be

```sql
SELECT * FROM mdl_user WHERE firstname ILIKE 'jose' ESCAPE '\'
```

Based on the collation of the table, MySQL will do the accent insensitive search by default. Unfortunately, PostgreSQL
requires adaption of the SQL-Query.

> In case of MySQL the result will probably include all our Joses, but for PostgreSQL we will only get Jose #1.
> Luckily, developers can easily implement AISE to make this work on PostgreSQL too!

## Developer information

In order to use AISE, developers must add the dependency in the version.php, e.g.

```php
$plugin->version = 2023121400;
$plugin->requires = 2022041900;
$plugin->component = 'local_damn_cool_plugin';
$plugin->release = '1.0';
$plugin->maturity = MATURITY_STABLE;
$plugin->dependencies = [
    'local_aise' => 2023121400,
];
```

When they want to make an accent insensitive search, they can replace the above code as follows:

```php
$searchname = $DB->sql_like_escape('jose');
$sqllike = \aise_like('firstname', '?');
$sqlparams = [ $searchname ];
$sql = "SELECT *
          FROM {user}
          WHERE {$sqllike}";
$records = $DB->get_records_sql($sql, $sqlparams);          
```

The AISE-Plugin automatically checks if the actual database is PostgreSQL or some other kind of database. For any database
other than PostgreSQL it will just use the Moodle data manipulation API without any changes. Only in case a PostgreSQL-database
is used, it will ensure that the required extension `unaccent` is enabled on the PostgreSQL-server and will modify the SQL-Query to work unaccented.
The resulting sql query will look like this:
```sql
SELECT * FROM mdl_user WHERE unaccent(firstname) ILIKE unaccent('jose') ESCAPE '\'
```

The function `aise_like()` has the same signature as $DB->sql_like() and thus also accepts the parameters `$casesensitive`, `$accentsensitive`, `$notlike` and `$escapechar`. This ensures complete compatibility with the Moodle data manipulation API with minimal effort by developers.
The only difference is, that aise_like() defaults to $casesenstive=false and $accentsensitive=false, whereas sql_like() defaults to $casesensitive=true and $accentsensitive=true.
