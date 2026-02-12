#!/bin/bash
#
# Frays Website Deployment Script
# Run this on the server to pull latest changes from GitHub
#
# Usage:
#   ./deploy.sh              # Normal deployment
#   ./deploy.sh --force     # Force pull (discard local changes)
#   ./deploy.sh --help      # Show help
#

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
GIT_REPO="https://github.com/junyso/Frays-Cottage-Bookkeeping-Services-Website.git"
BRANCH="main"

# Functions
log_info() {
    echo -e "${GREEN}ℹ${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}⚠${NC} $1"
}

log_error() {
    echo -e "${RED}✖${NC} $1"
}

show_help() {
    cat << EOF
Frays Website Deployment Script

Usage: $0 [OPTIONS]

OPTIONS:
    --force     Force pull, discard local changes
    --help      Show this help message

EXAMPLES:
    $0                  # Normal deployment
    $0 --force          # Force deployment (warning: discards local changes)

EOF
}

# Parse arguments
FORCE_PULL=false
while [[ $# -gt 0 ]]; do
    case $1 in
        --force)
            FORCE_PULL=true
            shift
            ;;
        --help)
            show_help
            exit 0
            ;;
        *)
            log_error "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
done

# Main deployment
main() {
    log_info "🚀 Starting Frays Website Deployment..."
    echo ""
    
    # Check if git repo exists
    if [ ! -d ".git" ]; then
        log_info "📦 Cloning repository..."
        git clone "$GIT_REPO" .
    else
        log_info "📥 Fetching latest changes..."
        git fetch origin
        
        if [ "$FORCE_PULL" = true ]; then
            log_warn "⚠️  Force mode: Discarding local changes..."
            git reset --hard origin/$BRANCH
        else
            log_info "🌿 Checking for updates on $BRANCH..."
            
            # Get current and latest commit
            CURRENT=$(git rev-parse HEAD)
            LATEST=$(git rev-parse origin/$BRANCH)
            
            if [ "$CURRENT" = "$LATEST" ]; then
                log_info "✅ Already up to date!"
                log_info "   Current commit: ${CURRENT:0:7}"
                exit 0
            else
                log_info "📝 Local: ${CURRENT:0:7} → Remote: ${LATEST:0:7}"
            fi
        fi
        
        log_info "🔄 Pulling changes..."
        git pull origin $BRANCH
    fi
    
    # Show deployment info
    echo ""
    log_info "✅ Deployment complete!"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  Latest commit: $(git rev-parse --short HEAD)"
    echo "  Author:       $(git log -1 --format='%an')"
    echo "  Date:         $(git log -1 --format='%ad' --date=short)"
    echo "  Message:      $(git log -1 --format='%s')"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    
    # Show changed files
    CHANGED=$(git diff --stat --name-only HEAD~1 2>/dev/null | wc -l)
    if [ "$CHANGED" -gt 0 ]; then
        log_info "Changed files:"
        git diff --stat --name-only HEAD~1
    fi
    
    echo ""
    log_info "🌐 Website ready at: http://localhost:8080"
    log_info "📝 Remember to restart web server if needed!"
}

# Run
main
