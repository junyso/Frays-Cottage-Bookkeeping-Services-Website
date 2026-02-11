# FRAYS COTTAGE BACKUP & RECOVERY STRATEGY

**Created:** 2026-02-10 09:30 CAT  
**Status:** NEEDS IMPLEMENTATION  
**Owner:** Ingrid + Julian

---

## CURRENT STATE

### ❌ PROBLEMS IDENTIFIED

1. **Git repository has NO commits**
   - All work is in `/Users/julianuseya/.openclaw/workspace/`
   - No version history
   - No backup history
   - Risk: Complete data loss if laptop fails

2. **OneDrive sync script exists but may not be running**
   - Script: `/Users/julianuseya/.openclaw/workspace/backup-workspace.sh`
   - Destination: `~/Library/CloudStorage/OneDrive-Personal/OpenClaw-Backup/`
   - Status: UNVERIFIED

3. **No automated backup schedule**
   - Manual backups only
   - No off-site redundancy
   - No disaster recovery plan

---

## CRITICAL FILES THAT MUST BE PROTECTED

### Level 1: Core Identity (PRIORITY)

| File | Location | Purpose |
|------|----------|---------|
| `SOUL.md` | `/workspace/` | My personality/behavior |
| `USER.md` | `/workspace/` | Your preferences/context |
| `IDENTITY.md` | `/workspace/` | My core identity |
| `AGENTS.md` | `/workspace/` | Agent rules |
| `TOOLS.md` | `/workspace/` | Tool configurations |

### Level 2: Knowledge & Memory

| File/Dir | Location | Purpose |
|----------|----------|---------|
| `memory/` | `/workspace/` | Session memories |
| `MEMORY.md` | `/workspace/` | Long-term memory |
| `memory/*.md` | `/workspace/` | Daily logs |

### Level 3: Sprint & Project Work

| File/Dir | Location | Purpose |
|----------|----------|---------|
| `SPRINT/` | `/workspace/` | Sprint documentation |
| `SYSTEMS/` | `/workspace/` | Systems exploration |
| `CLIENTS/` | `/workspace/` | Client databases |
| `CIPA_DATABASE/` | `/workspace/` | CIPA extraction |

### Level 4: Integrations

| File | Location | Purpose |
|------|----------|---------|
| `fa_api_gateway.php` | `/workspace/` | FA API (24KB, 700+ lines) |
| `fa_api_config.env` | `/workspace/` | API credentials template |
| `FA_API_DEPLOYMENT_GUIDE.md` | `/workspace/` | Documentation |

### Level 5: Configurations

| File | Location | Purpose |
|------|----------|---------|
| `openclaw.json` | `~/.openclaw/` | OpenClaw configuration |
| `credentials/` | `~/.openclaw/` | Stored credentials |
| `.htaccess` | `/workspace/` | FA API routing |

---

## RECOVERY CHECKLIST

### If laptop fails:

1. ☐ Install OpenClaw on new machine
2. ☐ Clone git repository (need to create one!)
3. ☐ Restore workspace from OneDrive
4. ☐ Reconfigure credentials
5. ☐ Test all integrations
6. ☐ Resume work

### Time to recover: **4-8 hours** (if backup works)

### Time to recover: **1-2 weeks** (if no backup)

---

## PROPOSED BACKUP STRATEGY

### Tier 1: Git (Version Control + Cloud)

```bash
# Create git repository
cd /Users/julianuseya/.openclaw/workspace
git init
git add .
git commit -m "Initial commit: Sprint setup, FA API, CIPA database"
git remote add origin https://github.com/julianuseya/openclaw-frayscottage.git
git push -u origin main
```

**Frequency:** Every major change

**Storage:** GitHub (free private repo)

**Pros:** Version history, branch capability, cloud backup

**Cons:** Large files (>100MB) may need LFS

---

### Tier 2: OneDrive (Live Sync)

```bash
# Already exists!
./backup-workspace.sh
```

**Frequency:** Daily automated via cron

**Storage:** OneDrive Personal (you have this)

**Pros:** Real-time sync, offline access

**Cons:** OneDrive only (no versioning)

---

### Tier 3: External Drive (Offline Backup)

```bash
# Weekly manual backup
rsync -avz /Users/julianuseya/.openclaw/workspace/ /Volumes/Backup/OpenClaw/
```

**Frequency:** Weekly

**Storage:** External hard drive

**Pros:** Air-gapped backup, fast restore

**Cons:** Manual, needs physical access

---

### Tier 4: GitHub Releases (Milestone Snapshots)

```bash
# Create release after each sprint
git tag -a v1.0 -m "Sprint 1 complete"
git push origin v1.0
```

**Frequency:** End of each sprint (6 days)

**Storage:** GitHub Releases

**Pros:** Permanent snapshots, downloadable

**Cons:** Manual process

---

## AUTOMATION PLAN

### Cron Jobs to Implement

```bash
# Daily OneDrive sync (2 AM)
0 2 * * * /Users/julianuseya/.openclaw/workspace/backup-workspace.sh >> /Users/julianuseya/.openclaw/logs/backup.log 2>&1

# Hourly git commit (if changes)
0 * * * * cd /Users/julianuseya/.openclaw/workspace && git add -A && git commit -m "Hourly sync $(date)" || true

# Weekly external backup (Sunday 3 AM)
0 3 * * 0 rsync -avz /Users/julianuseya/.openclaw/ /Volumes/Backup/OpenClaw/ >> /Users/julianuseya/.openclaw/logs/external-backup.log 2>&1
```

---

## RECOVERY PROCEDURES

### Scenario 1: Laptop Lost/Stolen

1. **Immediate:** Revoke API keys (OpenAI, OneDrive, WhatsApp)
2. **24 hours:** Get new laptop
3. **48 hours:** Install OpenClaw
4. **72 hours:** Restore from OneDrive
5. **1 week:** Full functionality restored

### Scenario 2: OpenClaw Corruption

1. **Check:** Is `~/.openclaw/` intact?
2. **Restore:** `git checkout HEAD -- .`
3. **Test:** Run `openclaw doctor`
4. **Resume:** Continue work

### Scenario 3: Workspace Accidental Delete

1. **Git:** `git checkout HEAD -- .`
2. **OneDrive:** Restore from OneDrive recycle bin
3. **Time Machine:** Restore from Mac backup (if enabled)

---

## VERIFICATION CHECKLIST

- [ ] Git repository created and pushed to GitHub
- [ ] OneDrive sync script tested and working
- [ ] Cron jobs configured
- [ ] External drive backup tested
- [ ] Recovery procedure documented
- [ ] All critical files in `~/.gitignore` reviewed
- [ ] Sensitive credentials NOT in git (use env vars)

---

## SECURITY CONSIDERATIONS

### NEVER commit to git:

- [ ] API keys (OpenAI, WhatsApp, OneDrive)
- [ ] Passwords or secrets
- [ ] Client financial data
- [ ] Personal identifiable information (PII)
- [ ] Database exports with sensitive data

### ALWAYS use:

- [ ] `.gitignore` for sensitive files
- [ ] Environment variables for credentials
- [ ] Encrypted storage for secrets
- [ ] Private repositories only

---

## SUCCESS METRICS

| Metric | Target | Measurement |
|--------|--------|-------------|
| Backup coverage | 100% critical files | Weekly audit |
| Recovery time | <8 hours | Tested quarterly |
| Git commits | Daily | Automated cron |
| OneDrive sync | Real-time | Hourly verification |
| External backup | Weekly | Cron logs |

---

## IMMEDIATE ACTIONS

### Today (Julian's Priority)

1. [ ] Create GitHub repository
2. [ ] First git commit (current state)
3. [ ] Push to GitHub
4. [ ] Test OneDrive sync script
5. [ ] Configure first cron job

### This Week

1. [ ] Set up automated git commits
2. [ ] Configure OneDrive cron job
3. [ ] Test external drive backup
4. [ ] Document recovery procedure
5. [ ] Create .gitignore file

---

## ONE-CLICK RECOVERY SCRIPT

```bash
#!/bin/bash
# RECOVERY SCRIPT - Run this after fresh OpenClaw install

echo "🔄 Recovering OpenClaw workspace..."
echo "1. Cloning git repository..."
git clone https://github.com/julianuseya/openclaw-frayscottage.git /Users/julianuseya/.openclaw/workspace

echo "2. Restoring from OneDrive (if needed)..."
rsync -av --delete ~/Library/CloudStorage/OneDrive-Personal/OpenClaw-Backup/ /Users/julianuseya/.openclaw/workspace/

echo "3. Restoring configurations..."
cp ~/Dropbox/Configs/openclaw.json ~/.openclaw/openclaw.json

echo "✅ Recovery complete!"
echo "Next steps:"
echo "- Run 'openclaw doctor' to verify"
echo "- Test API connections"
echo "- Resume work"
```

---

## FINAL NOTE

**Without proper backups, all our work is at risk.**

- **Today:** No git commits, no version history
- **Risk:** Complete data loss if laptop fails
- **Solution:** Implement backup strategy TODAY

---

**Action Required:** Julian, do you want me to:
1. **Implement backup strategy now?** (30 minutes)
2. **Continue sprint work?** (delay backups)
3. **Create recovery script only?** (quick fix)

Your choice. 🔐
