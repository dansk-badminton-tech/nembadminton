import re, html, json, sys

s = open(sys.argv[1]).read()
# Walk headers (tier names) and links in order
tokens = re.findall(r"<h\d[^>]*>(.*?)</h\d>|<td[^>]*class='[^']*'[^>]*>([^<]+)</td>|ShowStanding\('2', '2025', '(\d+)'[^>]*>([^<]*)<", s)
out = []
tier = None
for h, td, gid, name in tokens:
    if h or td:
        tier = html.unescape(re.sub('<[^>]+>', '', h or td)).strip()
    elif gid:
        out.append({'tier': tier, 'leagueGroupID': int(gid), 'name': html.unescape(name).strip()})
json.dump(out, open(sys.argv[2], 'w'), ensure_ascii=False, indent=1)
for o in out:
    print(o)
