#!/usr/bin/env python3
"""
SEOForge Deployment Launcher
Interactive deployment tool with guided setup
"""

import os
import sys
import subprocess
from pathlib import Path

def print_banner():
    """Print welcome banner"""
    banner = """
╔══════════════════════════════════════════════════════════════╗
║                    🚀 SEOForge Deployer 🚀                   ║
║                                                              ║
║              Production-Ready Python Deployment             ║
╚══════════════════════════════════════════════════════════════╝
"""
    print(banner)

def check_requirements():
    """Check if all requirements are met"""
    print("🔍 Checking requirements...")
    
    # Check Python version
    if sys.version_info < (3, 8):
        print("❌ Python 3.8+ required")
        return False
    
    # Check if pip is available
    try:
        subprocess.run(["pip", "--version"], capture_output=True, check=True)
        print("✅ pip available")
    except (subprocess.CalledProcessError, FileNotFoundError):
        print("❌ pip not found")
        return False
    
    # Check if AWS CLI is available
    try:
        subprocess.run(["aws", "--version"], capture_output=True, check=True)
        print("✅ AWS CLI available")
    except (subprocess.CalledProcessError, FileNotFoundError):
        print("⚠️  AWS CLI not found (optional for quick deploy)")
    
    return True

def install_dependencies():
    """Install required Python packages"""
    print("\n📦 Installing dependencies...")
    
    requirements_file = "deployment-requirements.txt"
    if not os.path.exists(requirements_file):
        print(f"❌ {requirements_file} not found")
        return False
    
    try:
        subprocess.run([
            sys.executable, "-m", "pip", "install", "-r", requirements_file
        ], check=True)
        print("✅ Dependencies installed successfully")
        return True
    except subprocess.CalledProcessError:
        print("❌ Failed to install dependencies")
        return False

def get_user_input():
    """Get deployment configuration from user"""
    print("\n📝 Deployment Configuration")
    print("=" * 40)
    
    config = {}
    
    # Deployment type
    print("\nSelect deployment type:")
    print("1. Quick Deploy (existing EC2 instance)")
    print("2. Full Infrastructure Deploy (creates everything)")
    print("3. Enhanced Deploy (with monitoring)")
    
    while True:
        choice = input("\nEnter choice (1-3): ").strip()
        if choice in ["1", "2", "3"]:
            config["type"] = choice
            break
        print("Invalid choice. Please enter 1, 2, or 3.")
    
    # Common configuration
    config["domain"] = input("Domain name (e.g., seoforge.example.com): ").strip()
    config["email"] = input("Email address: ").strip()
    
    if config["type"] == "1":  # Quick deploy
        config["ip"] = input("EC2 instance IP address: ").strip()
        config["key_file"] = input("SSH key file path: ").strip()
        config["ssh_user"] = input("SSH user (default: ec2-user): ").strip() or "ec2-user"
    
    elif config["type"] in ["2", "3"]:  # Full/Enhanced deploy
        config["key_name"] = input("AWS key pair name: ").strip()
        config["vpc_id"] = input("VPC ID: ").strip()
        config["subnet_id"] = input("Subnet ID: ").strip()
        config["region"] = input("AWS region (default: us-east-1): ").strip() or "us-east-1"
    
    return config

def validate_config(config):
    """Validate configuration"""
    print("\n🔍 Validating configuration...")
    
    if not config["domain"]:
        print("❌ Domain name is required")
        return False
    
    if not config["email"]:
        print("❌ Email address is required")
        return False
    
    if config["type"] == "1":
        if not config["ip"]:
            print("❌ IP address is required")
            return False
        
        if not config["key_file"] or not os.path.exists(config["key_file"]):
            print("❌ SSH key file not found")
            return False
    
    elif config["type"] in ["2", "3"]:
        if not config["key_name"]:
            print("❌ AWS key pair name is required")
            return False
        
        if not config["vpc_id"]:
            print("❌ VPC ID is required")
            return False
        
        if not config["subnet_id"]:
            print("❌ Subnet ID is required")
            return False
    
    print("✅ Configuration validated")
    return True

def check_environment_variables():
    """Check for required environment variables"""
    print("\n🔐 Checking environment variables...")
    
    required_vars = ["OPENAI_API_KEY", "ANTHROPIC_API_KEY", "GOOGLE_AI_API_KEY"]
    missing_vars = []
    
    for var in required_vars:
        if not os.getenv(var):
            missing_vars.append(var)
    
    if missing_vars:
        print("⚠️  Missing environment variables:")
        for var in missing_vars:
            print(f"   - {var}")
        
        print("\nYou can set them now or update them after deployment.")
        choice = input("Continue anyway? (y/n): ").strip().lower()
        return choice == "y"
    
    print("✅ All environment variables found")
    return True

def run_deployment(config):
    """Run the selected deployment"""
    print("\n🚀 Starting deployment...")
    
    if config["type"] == "1":  # Quick deploy
        cmd = [
            sys.executable, "quick_deploy.py",
            "--ip", config["ip"],
            "--key", config["key_file"],
            "--domain", config["domain"],
            "--user", config["ssh_user"]
        ]
    
    elif config["type"] == "2":  # Full deploy
        cmd = [
            sys.executable, "deploy.py",
            "--domain", config["domain"],
            "--email", config["email"],
            "--key-name", config["key_name"],
            "--vpc-id", config["vpc_id"],
            "--subnet-id", config["subnet_id"],
            "--region", config["region"]
        ]
    
    elif config["type"] == "3":  # Enhanced deploy
        # Create config file for enhanced deploy
        create_enhanced_config(config)
        cmd = [sys.executable, "deploy_enhanced.py", "deploy"]
    
    try:
        print(f"Running: {' '.join(cmd)}")
        subprocess.run(cmd, check=True)
        print("\n🎉 Deployment completed successfully!")
        return True
    except subprocess.CalledProcessError as e:
        print(f"\n❌ Deployment failed: {e}")
        return False

def create_enhanced_config(config):
    """Create configuration file for enhanced deployment"""
    config_content = f"""
application:
  name: seoforge
  domain: {config['domain']}
  email: {config['email']}
  environment: production

aws:
  region: {config['region']}
  key_name: {config['key_name']}
  vpc_id: {config['vpc_id']}
  subnet_ids:
    - {config['subnet_id']}

infrastructure:
  ec2:
    instance_type: t3.large
    ami_id: ami-0c02fb55956c7d316
  rds:
    instance_class: db.t3.small
    allocated_storage: 20
  elasticache:
    node_type: cache.t3.micro

security:
  allowed_ssh_cidrs:
    - 0.0.0.0/0
  enable_waf: false

monitoring:
  enable_cloudwatch: true
  enable_prometheus: true
  alert_email: {config['email']}

deployment:
  git_repository: https://github.com/your-org/seoforge-mcp-server.git
  git_branch: main
  health_check_timeout: 300
  rollback_on_failure: true
"""
    
    with open("deployment_config.yaml", "w") as f:
        f.write(config_content.strip())
    
    print("✅ Enhanced deployment config created")

def print_next_steps(config):
    """Print next steps after deployment"""
    print("\n📋 Next Steps:")
    print("=" * 40)
    
    if config["type"] == "1":
        print(f"1. Update DNS records to point to {config['ip']}")
        print("2. Configure SSL certificate")
        print("3. Update API keys in /opt/seoforge/.env")
        print("4. Test the application")
        print(f"\n🔗 Access your application:")
        print(f"   - Health check: http://{config['ip']}/health")
        print(f"   - API: http://{config['ip']}/api/")
    
    else:
        print("1. Check AWS console for created resources")
        print("2. Update DNS records")
        print("3. Configure SSL certificate")
        print("4. Update API keys")
        print("5. Set up monitoring alerts")
    
    print(f"\n📧 Domain: {config['domain']}")
    print("📚 Documentation: PYTHON_DEPLOYMENT_GUIDE.md")

def main():
    """Main function"""
    print_banner()
    
    # Check requirements
    if not check_requirements():
        print("\n❌ Requirements check failed. Please install missing dependencies.")
        sys.exit(1)
    
    # Install dependencies
    if not install_dependencies():
        print("\n❌ Failed to install dependencies.")
        sys.exit(1)
    
    # Get configuration
    config = get_user_input()
    
    # Validate configuration
    if not validate_config(config):
        print("\n❌ Configuration validation failed.")
        sys.exit(1)
    
    # Check environment variables
    if not check_environment_variables():
        print("\n❌ Environment variable check failed.")
        sys.exit(1)
    
    # Confirm deployment
    print(f"\n🔍 Deployment Summary:")
    print(f"   Type: {['', 'Quick Deploy', 'Full Deploy', 'Enhanced Deploy'][int(config['type'])]}")
    print(f"   Domain: {config['domain']}")
    print(f"   Email: {config['email']}")
    
    if config["type"] == "1":
        print(f"   Target IP: {config['ip']}")
    
    confirm = input("\nProceed with deployment? (y/n): ").strip().lower()
    if confirm != "y":
        print("Deployment cancelled.")
        sys.exit(0)
    
    # Run deployment
    if run_deployment(config):
        print_next_steps(config)
    else:
        print("\n❌ Deployment failed. Check logs for details.")
        sys.exit(1)

if __name__ == "__main__":
    main()
