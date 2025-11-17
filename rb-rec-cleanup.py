#!/usr/bin/env python
# -*- coding: utf8 -*-

import MySQLdb
from contextlib import closing
from glob import glob
import os
from datetime import datetime, timedelta
from dateutil.relativedelta import relativedelta

import login


FILENAME_PATTERNS = [
    '/var/www/content/Aufnahmen/*.mp3',
    '/var/www/content/Aufnahmen/*.m4a',
    '/var/www/content/Aufnahmen/*.aac'
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


def delete_old_recordings(connection, cursor):
    """Delete recordings older than X months based on settings."""

    # Get auto-delete settings
    cursor.execute(
        "SELECT wert FROM settings WHERE name = 'auto_delete_enabled'"
    )
    result = cursor.fetchone()
    if not result or result[0] != '1':
        # Auto-delete is disabled
        return

    cursor.execute(
        "SELECT wert FROM settings WHERE name = 'auto_delete_months'"
    )
    result = cursor.fetchone()
    if not result:
        # No months setting found, default to 3 months
        months = 3
    else:
        try:
            months = int(result[0])
        except ValueError:
            months = 3

    # Calculate cutoff date
    cutoff_date = datetime.now() - relativedelta(months=months)

    # Delete old recordings from database
    db_request = 'SELECT id, datei FROM aufnahmen WHERE datum < %s'
    cursor.execute(db_request, (cutoff_date.strftime('%Y-%m-%d'),))
    old_recordings = cursor.fetchall()

    for recording_id, filename in old_recordings:
        # Delete file from filesystem
        file_path = PATH_RECORDINGS + filename
        if os.path.isfile(file_path):
            try:
                os.remove(file_path)
            except OSError as e:
                print('Error deleting file {}: {}'.format(file_path, e))

        # Delete from database
        cursor.execute('DELETE FROM aufnahmen WHERE id = %s', (recording_id,))

    connection.commit()

    if old_recordings:
        print('Deleted {} old recording(s) older than {} months'.format(
            len(old_recordings), months
        ))


def main():

    with closing(MySQLdb.connect(
            login.DB_HOST, login.DB_USER,
            login.DB_PASSWORD, login.DB_DATABASE
            )) as connection:

        with closing(connection.cursor()) as cursor:

            delete_files_without_db_entry(cursor)
            delete_db_entries_without_file(connection, cursor)
            delete_old_recordings(connection, cursor)


if __name__ == '__main__':
    main()
