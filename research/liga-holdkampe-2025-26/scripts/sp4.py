import re, html, sys, json

s = open(sys.argv[1]).read()
rows = re.findall(r'<tr[^>]*>.*?</tr>', s, re.S)
out = []
rnd = None
for r in rows:
    m = re.search(r"class='roundheader'><td[^>]*>(.*?)</td>", r)
    if m:
        rnd = html.unescape(m.group(1)).strip()
        continue
    if "class='time'" not in r:
        continue
    tds = re.findall(r"<td class='(\w+)'[^>]*>(.*?)</td>", r, re.S)
    d = {'roundHeader': rnd}
    teams = []
    for c, v in tds:
        txt = html.unescape(re.sub(r'<[^>]+>', ' ', v)).replace('‑', '-').strip()
        txt = re.sub(r'\s+', ' ', txt)
        if c == 'team':
            tid = re.search(r"ShowStanding\('3', '\d+', '\d*', '\d*', '\d*', '(\d*)'", v)
            teams.append((txt, tid.group(1) if tid else None))
        else:
            d.setdefault(c, []).append(txt)
            if c == 'time':
                d['time_raw'] = v
    d['teams'] = teams
    out.append(d)
json.dump(out, open(sys.argv[2], 'w'), ensure_ascii=False, indent=1)
print(len(out), 'matches; rounds:', sorted(set(str(o['roundHeader']) for o in out)))
odd = [o for o in out if '(' in o['time_raw'] or '<' in o['time_raw']]
print('time cells with markup/parens:', len(odd))
for o in odd[:8]:
    print(' ', o['time_raw'][:200], o.get('matchno'))
print('classes:', set(k for o in out for k in o))
