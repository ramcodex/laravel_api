✅ This Covers All Real-World Git Usage:

🔹 Basic Setup & Info
git --version, git config, git config --list

🔹 Repository Management
git init, git clone, git remote -v

🔹 Tracking Changes
git status, git add, git diff, git diff --cached, git commit, git log, git show, git reflog

🔹 Branching & Collaboration
git branch, git checkout, git merge, git rebase, git push, git pull

🔹 Undo / Restore
git restore, git restore --staged, git reset --soft, git reset --hard, git stash, git stash pop

🔹 Debugging & Inspection
git blame, git reflog

git checkout -b branch-name   # create & switch
git push -u origin branch-name
git checkout main             # back to main
git branch -d login-feature
git push origin --delete login-feature


---------------------------------------------------------------$_COOKIE

git --version                          # Check installed Git version.
git config --global user.name "Ram"   # Set your Git username globally.
git config --global user.email "ram@gmail.com"  # Set your Git email globally.
git config --list                     # View all Git configurations.
git init                              # Initialize a new Git repository.
git status                            # Show current status of files (staged, unstaged, untracked).
git add index.html                    # Stage index.html file for commit.
git commit -m "This is first post"    # Save staged changes with a message.
git log                               # View the commit history.
git branch login-feature              # Create a new branch named login-feature.
git checkout login-feature            # Switch to the login-feature branch.
git diff                              # Show unstaged changes (file vs last commit).
git diff --cached                     # Show staged changes (ready to commit).
git show <commit-id>                  # Show details of a specific commit.
git remote -v                         # List remote repositories (like GitHub).
git push origin branch-name           # Push local branch to GitHub.
git pull origin branch-name           # Fetch and merge changes from GitHub to local.
git clone <repo-url>                  # Copy a remote repo to your local system.
git restore <file>                    # Discard changes in working directory (undo edits).
git restore --staged <file>           # Unstage a file (keep changes, remove from staging).
git reset --soft HEAD^                # Undo last commit but keep files staged.
git reset --hard HEAD^                # Undo last commit and remove all changes.
git merge branch-name                 # Merge another branch into current branch.
git rebase branch-name                # Reapply commits on top of another branch (cleaner merge).
git stash                             # Temporarily save uncommitted changes.
git stash pop                         # Restore last stashed changes.
git blame <file>                      # Show who last modified each line of a file.
git reflog                            # Show history of all Git HEAD movements (commits, resets, etc).
