"""Polite fetcher for GetLeagueStanding. Usage:
fetch.py <outprefix> <subPage> <seasonID> <leagueGroupID> <ageGroupID> <regionID> <teamID> <matchID> <clubID>
Writes <outprefix>.json and <outprefix>.html; skips if .json already exists (disk cache).
"""
import json, os, re, sys, time, urllib.request
from datetime import datetime

RAW = '/tmp/bp/raw'
token = re.search(r"SR_CallbackContext = '([^']*)", open(f'{RAW}/home.html').read()).group(1)


def gls(prefix, sub, season, group='', age='', region='', team='', match='', club='', player=''):
    path = f'{RAW}/{prefix}.json'
    if not os.path.exists(path):
        time.sleep(1.5)
        body = json.dumps({'callbackcontextkey': token, 'subPage': str(sub), 'seasonID': str(season),
                           'leagueGroupID': str(group), 'ageGroupID': str(age), 'regionID': str(region),
                           'leagueGroupTeamID': str(team), 'leagueMatchID': str(match), 'clubID': str(club),
                           'playerID': str(player)}).encode()
        req = urllib.request.Request(
            'https://www.badmintonplayer.dk/SportsResults/Components/WebService1.asmx/GetLeagueStanding',
            data=body, headers={'Content-Type': 'application/json; charset=utf-8', 'User-Agent': 'Mozilla/5.0'})
        t = time.time()
        with urllib.request.urlopen(req, timeout=60) as r:
            data = r.read()
            status = r.status
        open(path, 'wb').write(data)
        with open('/tmp/bp/requests.log', 'a') as log:
            log.write(f'{datetime.now():%T} {status} {time.time()-t:.2f}s GLS {sub} {group} {team} {match} {club}\n')
    d = json.load(open(path))['d']
    h = d.get('html') or d.get('Html') or ''
    open(f'{RAW}/{prefix}.html', 'w').write(h)
    return h


if __name__ == '__main__':
    a = sys.argv[1:]
    gls(*a)
