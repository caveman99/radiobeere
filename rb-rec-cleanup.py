#!/usr/bin/env python
# -*- coding: utf8 -*-

import MySQLdb
from contextlib import closing
from glob import glob
import os

import login


FILENAME_PATTERNS = [
    '/var/www/content/Aufnahmen/*.mp3',
    '/var/www/content/Aufnahmen/*.m4a'
]
PATH_RECORDINGS = '/var/www/content/Aufnahmen/'


def delete_files_without_db_entry(cursor):

    for pattern in FILENAME_PATTERNS:
        for full_path in glob(pattern):
            filename = os.path.basename(full_path)
            db_request = 'SELECT COUNT(*) FROM aufnahmen WHERE datei = %s'
            cursor.execute(db_request, (filename,))
            check = cursor.fetchone()[0]
            if check == 0:
                os.remove(full_path)


def delete_db_entries_without_file(connection, cursor):

    cursor.execute('SELECT datei FROM aufnahmen')
    result = cursor.fetchall()
    for filename in result:
        check = os.path.isfile(PATH_RECORDINGS + filename[0])
        if check is False:
            db_request = 'DELETE FROM aufnahmen WHERE datei = %s'
            cursor.execute(db_request, (filename[0],))
            connection.commit()


def main():

    with closing(MySQLdb.connect(
            login.DB_HOST, login.DB_USER,
            login.DB_PASSWORD, login.DB_DATABASE
            )) as connection:

        with closing(connection.cursor()) as cursor:

            delete_files_without_db_entry(cursor)
            delete_db_entries_without_file(connection, cursor)


if __name__ == '__main__':
    main()
