import json, glob, re, collections

dh = {g['leagueGroupID']: g for g in json.load(open('/tmp/bp/raw/sp1.parsed.json'))}
CLUBS = {-3: 'Vendsyssel', 1099: 'Skovshoved', 1094: 'Hvidovre', 25: 'Højbjerg', 1092: 'Gentofte', 42: 'Værløse',
         18: 'Greve', 39: 'Solrød Strand', 1392: 'Odense OBK', -2: 'Team Skælskør-Slagelse'}
team_groups = {}  # (club, teamname) -> set(groups)
for c in CLUBS:
    for t in json.load(open(f'/tmp/bp/raw/club{c}_sp6.parsed.json')):
        if 'HYGGE' in t['tier']:
            continue
        team_groups.setdefault((c, t['team']), set()).add(t['leagueGroupID'])


def norm(n):
    return re.sub(r'\s*\([A-ZÆØÅ]+\)$', '', n).strip()


rows = []
for (c, team), groups in team_groups.items():
    for g in groups:
        for m in json.load(open(f'/tmp/bp/raw/pulje{g}_sp4.parsed.json')):
            if m['roundHeader'] is None:
                continue
            names = [norm(t[0]) for t in m['teams'] if t[1]]
            if team in names:
                side = 'home' if names[0] == team else 'away'
                opp = names[1] if side == 'home' else names[0]
                rh = m['roundHeader']
                rm = re.match(r'(.*?)\s+(\d\d-\d\d-\d{4})$', rh)
                rows.append({'club': c, 'team': team, 'tier': dh[g]['tier'], 'pulje': dh[g]['name'], 'group': g,
                             'round': rm.group(1) if rm else rh, 'roundDate': rm.group(2) if rm else None,
                             'time': m['time'][0], 'match': m['matchno'][0], 'side': side, 'opponent': opp,
                             'venue': m['venue'][-1] if m.get('venue') else None, 'score': m.get('score', [''])[0],
                             'wo': m.get('wo', [''])[0]})
json.dump(rows, open('/tmp/bp/raw/liga_clubs_holdkampe.json', 'w'), ensure_ascii=False, indent=1)
print('holdkampe (team-sides):', len(rows), 'distinct match ids:', len({r['match'] for r in rows}))
print('teams with matches:', len({(r['club'], r['team']) for r in rows}), 'of', len(team_groups))
print('round labels:', collections.Counter(r['round'] for r in rows))
print('per club:', collections.Counter(CLUBS[r['club']] for r in rows))
moved = [r for r in rows if r['roundDate'] and r['roundDate'] not in r['time']]
print('moved off round date:', len(moved))
for r in moved[:6]:
    print('  ', r['team'], r['tier'], r['round'], r['roundDate'], '->', r['time'])
print('opponents "Ukendt":', [(r['team'], r['round'], r['match'], r['time']) for r in rows if 'kendt' in r['opponent'].lower()])
print('odd scores:', [(r['team'], r['round'], r['match'], r['score'], r['wo']) for r in rows if not re.fullmatch(r'\d+-\d+', r['score'] or '') or r['wo']])
print('team names:', sorted({r['team'] for r in rows}))
no = set(team_groups) - {(r['club'], r['team']) for r in rows}
print('teams w/o rows:', no)
# per club: rounds with >=1 holdkamp
for c in CLUBS:
    rs = sorted({r['round'] for r in rows if r['club'] == c}, key=lambda x: [int(p) for p in re.findall(r'\d+', x)])
    print(CLUBS[c], rs)
