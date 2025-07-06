# CoFirst LMS - Git Branch Strategy

## Branch Naming Convention

Each phase will have its own feature branch following this pattern:
- `cf-001-phase-1` - Foundation & Photo Management
- `cf-002-phase-2` - Certificate System
- `cf-003-phase-3` - Communication System
- `cf-004-phase-4` - Gamification System
- `cf-005-phase-5` - Testing & Deployment

## Workflow

### 1. Creating Phase Branches
```bash
# Create new phase branch from main
git checkout main
git pull origin main
git checkout -b cf-XXX-phase-X

# Example for Phase 1 (already created):
git checkout -b cf-001-phase-1
```

### 2. Working on a Phase
```bash
# Make sure you're on the correct branch
git branch --show-current

# Regular commits during development
git add .
git commit -m "feat: implement photo upload functionality"

# Push to remote
git push -u origin cf-001-phase-1
```

### 3. Completing a Phase
```bash
# When phase is complete, push final changes
git add .
git commit -m "feat: complete Phase 1 - Foundation & Photo Management"
git push origin cf-001-phase-1

# Create Pull Request on GitHub
# After PR is approved and merged, delete local branch
git checkout main
git pull origin main
git branch -d cf-001-phase-1
```

### 4. Starting Next Phase
```bash
# Always start from updated main
git checkout main
git pull origin main
git checkout -b cf-002-phase-2
```

## Current Status

### Active Branch: `cf-001-phase-1`
**Phase 1: Foundation & Photo Management**
- [ ] Configure file storage system
- [ ] Create media database structure
- [ ] Implement photo upload functionality
- [ ] Create UI components for photo management

### Upcoming Branches:
- `cf-002-phase-2` - Certificate System
- `cf-003-phase-3` - Communication System
- `cf-004-phase-4` - Gamification System
- `cf-005-phase-5` - Testing & Deployment

## Git Commands Quick Reference

```bash
# Check current branch
git branch --show-current

# List all branches
git branch -a

# Switch between branches
git checkout branch-name

# Create and switch to new branch
git checkout -b new-branch-name

# Push new branch to remote
git push -u origin branch-name

# Delete local branch
git branch -d branch-name

# Delete remote branch
git push origin --delete branch-name
```

## Commit Message Convention

Use conventional commits for clear history:
- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting, etc)
- `refactor:` - Code refactoring
- `test:` - Adding tests
- `chore:` - Maintenance tasks

Examples:
```bash
git commit -m "feat: add profile photo upload"
git commit -m "fix: image resize validation"
git commit -m "docs: update photo upload guide"
```

## Phase Completion Checklist

Before creating a Pull Request:
1. [ ] All phase features implemented
2. [ ] Code tested locally
3. [ ] Database migrations run successfully
4. [ ] No console errors
5. [ ] Code follows Laravel conventions
6. [ ] Documentation updated
7. [ ] Commit messages are clear
8. [ ] Branch is up to date with main

---

*Update this document as new phases are started or completed.*