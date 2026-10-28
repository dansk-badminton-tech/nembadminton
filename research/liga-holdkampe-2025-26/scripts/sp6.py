import re, html, json, sys

dh = {g['leagueGroupID']: g for g in json.load(open('/tmp/bp/raw/sp1.parsed.json'))}
s = open(sys.argv[1]).read()
head = re.search(r"<h2[^>]*>(.*?)</h2>|<td[^>]*>([^<]*20\d\d/20\d\d)</td>", s)
rows = re.findall(r"<tr class='grouprow'>(.*?)</tr>", s, re.S)
out = []
for r in rows:
    tds = re.findall(r'<td[^>]*>(.*?)</td>', r, re.S)
    m = re.search(r"ShowStanding\('2', '(\d+)', '(\d+)', '(\d*)', '(\d*)'", r)
    if not m:
        continue
    gid = int(m.group(2))
    if gid not in dh:
        continue
    team = html.unescape(re.sub('<[^>]+>', '', tds[0])).strip()
    out.append({'team': team, 'leagueGroupID': gid, 'tier': dh[gid]['tier'], 'pulje': dh[gid]['name']})
for o in out:
    print(o)
if len(sys.argv) > 2:
    json.dump(out, open(sys.argv[2], 'w'), ensure_ascii=False, indent=1)
