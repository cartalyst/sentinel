#Create the Underlying DB Tables for Sentinel

Sentinel requires just a few tables to run properly.

Some frameworks will preconfigure the Sentinel implementation.

If yours does not or for whatever reason you need to install manually:

Run or source mysql.sql for MySQL 5.5.x and below & forks of similar code base

Run or source mysql-5.6+.sql for MySQL 5.6.x and up & forks of similar code base 

* You can also use phinx to setup these table. 
    * You would have to run `composer require  robmorgan/phinx "^0.12.0" --dev` and then setup phinx to run the migration located in **./schema/phinx-migrations/20201208222318_add_cartalyst_tables_to_db.php** . See [here](https://book.cakephp.org/phinx/0/en/) for phinx documentation.
    * Setting up the db tables this way would allow you to be able to use other db engines like sqlite, PostgreSql and Sql Server with sentinel