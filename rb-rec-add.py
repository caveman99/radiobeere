#!/usr/bin/env python
# -*- coding: utf8 -*-

import MySQLdb
from contextlib import closing
from glob import glob
import os
import time
import datetime
from email.utils import formatdate

from mutagen.mp3 import MP3
from mutagen.id3 import ID3, TIT2, TPE1, TALB, APIC
from mutagen.mp4 import MP4, MP4Cover
from mutagen.aac import AAC

import login


FILENAME_PATTERNS = [
    '/var/www/content/Aufnahmen/aufnahme_fertig_*.mp3',
    '/var/www/content/Aufnahmen/aufnahme_fertig_*.m4a',
    '/var/www/content/Aufnahmen/aufnahme_fertig_*.aac'
]
DATE_TIME_FORMAT = '%Y_%m_%d_%H_%M_%S'
PODCAST_IMG_PATH = '/var/www/content/img/podcast/'


def audio_length(filename):

    # Determine file type and use appropriate mutagen class
    _, extension = os.path.splitext(filename)
    extension = extension.lower()

    if extension == '.mp3':
        audio = MP3(filename)
    elif extension == '.m4a':
        audio = MP4(filename)
    elif extension == '.aac':
        audio = AAC(filename)
    else:
        raise ValueError('Unsupported file format: {}'.format(extension))

    length = str(
        datetime.timedelta(seconds=int(audio.info.length))
    )
    length_bytes = os.path.getsize(filename)

    return length, length_bytes


def extract_metadata(filename):

    _, _, station_alias, recording_time = filename.split('_', 3)
    recording_time = (
            datetime.datetime.strptime(
            recording_time, DATE_TIME_FORMAT)
    )

    return station_alias, recording_time


def get_station_name(connection, station_alias):

    with closing(connection.cursor()) as cursor:
        cursor.execute(
                'SELECT name FROM sender WHERE alias=%s', (
                station_alias,)
        )
        row = cursor.fetchone()

        if not row:
            raise KeyError('Sender nicht in der Datenbank vorhanden')

        return row[0]


def add_metadata_tags(path, station, station_alias, recording_time):

    # Determine file type
    _, extension = os.path.splitext(path)
    extension = extension.lower()

    # Raw AAC files (.aac) don't support metadata tags
    # Only MP3 and M4A (AAC in MP4 container) support tags
    if extension == '.aac':
        # Skip metadata tagging for raw AAC files
        return

    podcast_img = PODCAST_IMG_PATH + station_alias + '.jpg'
    if os.path.isfile(podcast_img) is False:
        podcast_img = PODCAST_IMG_PATH + 'default.jpg'

    title = '{0}, {1:%d.%m.%Y, %H:%M} Uhr'.format(station, recording_time)
    album = '{0:%Y-%m-%d}'.format(recording_time)

    if extension == '.mp3':
        # ID3 tags for MP3
        audio = ID3()
        audio.save(path)
        audio = ID3(path)
        audio.add(TIT2(encoding=3, text=title))
        audio.add(TPE1(encoding=3, text=station))
        audio.add(TALB(encoding=3, text=album))
        audio.add(APIC(
                encoding = 3,
                mime = 'image/jpeg',
                type = 3,
                desc = u'Cover',
                data = open(podcast_img, 'rb').read()
                )
        )
        audio.save(v2_version=3)

    elif extension == '.m4a':
        # MP4 tags for M4A (AAC in MP4 container)
        audio = MP4(path)
        audio['\xa9nam'] = title  # Title
        audio['\xa9ART'] = station  # Artist
        audio['\xa9alb'] = album  # Album

        # Add cover art
        with open(podcast_img, 'rb') as img:
            audio['covr'] = [MP4Cover(img.read(), imageformat=MP4Cover.FORMAT_JPEG)]

        audio.save()


def write_to_db(connection, recording_time, station,
                new_filename, length, length_bytes):

    rec_date = '{0:%Y-%m-%d}'.format(recording_time)
    rec_time = '{0:%H:%M}'.format(recording_time)
    timestamp = int(recording_time.strftime("%s"))
    pub_date = formatdate(time.time(), True)

    with closing(connection.cursor()) as cursor:

        cursor.execute('INSERT INTO aufnahmen \
        (datum, uhrzeit, sender, datei, zeitstempel, laenge, bytes, pubdate) \
        VALUES (%s,%s,%s,%s,%s,%s,%s,%s)',
                (rec_date, rec_time, station, new_filename,
                 timestamp, length, length_bytes, pub_date))

        connection.commit()


def main():

    with closing(MySQLdb.connect(
            login.DB_HOST, login.DB_USER,
            login.DB_PASSWORD, login.DB_DATABASE)) as connection:

        for pattern in FILENAME_PATTERNS:
            for path in glob(pattern):

                directory = os.path.dirname(path)
                filename, extension = os.path.splitext(os.path.basename(path))

                station_alias, recording_time = extract_metadata(filename)
                station = get_station_name(connection, station_alias)
                new_filename = '{0}_{1:%Y-%m-%d}_{1:%H-%M}{2}'.format(
                        station_alias, recording_time, extension
                )

                add_metadata_tags(path, station, station_alias, recording_time)

                length, length_bytes = audio_length(path)

                write_to_db(
                        connection, recording_time, station,
                        new_filename, length, length_bytes
                )
                os.rename(path, (os.path.join(directory, new_filename)))


if __name__ == '__main__':
    main()
