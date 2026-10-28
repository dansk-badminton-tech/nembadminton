import sys, json, re, html
sys.path.insert(0, '/tmp/bpscripts')
from fetch import gls

rows = {r['match']: r for r in json.load(open('/tmp/bp/raw/liga_clubs_holdkampe.json'))}


def parse(h):
    """Return info dict, list of individual matches, reserves."""
    info = {}
    for lbl, val in re.findall(r"<td class='lbl'>(.*?)</td>\s*<td class='val'[^>]*>(.*?)</td>", h, re.S):
        info[html.unescape(re.sub('<[^>]+>', '', lbl)).strip()] = re.sub(r'\s+', ' ', html.unescape(re.sub(r'<br\s*/?>', ' / ', re.sub(r'<(?!br)[^>]+>', '', val)))).strip()
    games = []
    sch = re.search(r"<table class='matchresultschema showmatch'>(.*?)</table>", h, re.S)
    if sch:
        for tr in re.findall(r'<tr>(.*?)</tr>', sch.group(1), re.S):
            disc = re.search(r"<td class='discipline'>([^<]*)", tr)
            if not disc:
                continue
            sides = []
            for cls, td in re.findall(r"<td class='(player\w*)'>(.*?)</td>", tr, re.S):
                players = [(int(i), html.unescape(n)) for i, n in re.findall(r"VisSpiller/#(\d+)'[^>]*>([^<]*)</a>", td)]
                rest = html.unescape(re.sub('<[^>]+>', ' ', re.sub(r"<div class='teamhdr'>.*?</div>", '', td))).strip()
                extra = re.sub(r'\s+', ' ', rest)
                for _, n in players:
                    extra = extra.replace(n, '').strip()
                sides.append({'winner': cls == 'playerwinner', 'players': players, 'other_text': extra or None})
            sets = [re.sub(r'\s+', ' ', html.unescape(re.sub('<[^>]+>', '', s))).replace('\xa0', ' ').strip() for s in re.findall(r"<td class='result'>(.*?)</td>", tr, re.S)]
            wo = re.search(r"<td class='wo'>(.*?)</td>", tr, re.S)
            games.append({'discipline': disc.group(1).strip(), 'home': sides[0] if sides else None, 'away': sides[1] if len(sides) > 1 else None,
                          'sets': [x for x in sets if x], 'wo': html.unescape(re.sub('<[^>]+>', '', wo.group(1))).strip() if wo else None})
    reserves = []
    for i, block in re.findall(r"id='PanelSubstituteList(\d)'>(.*?)</div>", h, re.S):
        for x, xn, y, yn in re.findall(r"#(\d+)'[^>]*>([^<]*)</a><br /><span class='label'>for</span> <a [^>]*#(\d+)'[^>]*>([^<]*)</a>", block):
            reserves.append({'side': 'home' if i == '0' else 'away', 'reserve': [int(x), html.unescape(xn)], 'for': [int(y), html.unescape(yn)]})
    return info, games, reserves


if __name__ == '__main__':
    for mid in sys.argv[1:]:
        r = rows.get(mid)
        h = gls(f'm{mid}', 5, 2025, group=r['group'] if r else '', age=1, region=1, match=mid)
        info, games, reserves = parse(h)
        out = {'leagueMatchID': mid, 'listRow': r, 'info': info, 'games': games, 'reserves': reserves}
        # drop team-leader contact details (name/email/phone) from info
        for k in ('Hjemmehold', 'Udehold'):
            if k in info:
                info[k] = info[k].split(' / ')[0]
        json.dump(out, open(f'/tmp/bp/raw/m{mid}.parsed.json', 'w'), ensure_ascii=False, indent=1)
        print('=====', mid, r and (r['team'], r['round'], r['pulje'], r['opponent']))
        print(' info keys:', list(info))
        for g in games:
            fmt = lambda s: s and ([n for _, n in s['players']], s['other_text'], 'W' if s['winner'] else '')
            print('  ', g['discipline'], fmt(g['home']), fmt(g['away']), g['sets'], g['wo'])
        print(' reserves:', reserves)
