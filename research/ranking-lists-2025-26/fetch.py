"""Polite client for badmintonplayer.dk WebService1.asmx.
Usage: python3 -I bp.py <outdir> <jobs.json>
jobs.json: [{"method": "...", "name": "file.json", "params": {...}}, ...]; skips jobs whose output exists."""
import json, sys, time, re, urllib.request, os

UA = "Mozilla/5.0 (research script; low rate)"
out, jobs_file = sys.argv[1:3]
tok_path = os.path.join(out, "token.txt")
if not os.path.exists(tok_path):
    html = urllib.request.urlopen(urllib.request.Request("https://badmintonplayer.dk/", headers={"User-Agent": UA})).read().decode()
    open(tok_path, "w").write(re.search(r"SR_CallbackContext = '([0-9A-F]+)'", html).group(1))
    time.sleep(2)
token = open(tok_path).read().strip()
for job in json.load(open(jobs_file)):
    path = os.path.join(out, job["name"])
    if os.path.exists(path):
        print("cached", job["name"]); continue
    params = dict(job["params"]); params["callbackcontextkey"] = token
    req = urllib.request.Request(
        "https://badmintonplayer.dk/SportsResults/Components/WebService1.asmx/" + job["method"],
        data=json.dumps(params).encode(), headers={"User-Agent": UA, "Content-Type": "application/json; charset=utf-8"})
    t = time.time()
    try:
        body = urllib.request.urlopen(req, timeout=60).read()
    except Exception as e:
        print("ERR", job["name"], e); time.sleep(3); continue
    open(path, "wb").write(body)
    print(job["name"], len(body), "bytes", round(time.time() - t, 2), "s")
    time.sleep(2)
