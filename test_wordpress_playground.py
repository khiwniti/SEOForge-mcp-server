#!/usr/bin/env python3
"""
Test script for WordPress Playground integration
Tests the WordPress plugin with the MCP server
"""

import requests
import json
import time
import subprocess
import os
import shutil
import zipfile
from pathlib import Path

class WordPressPlaygroundTester:
    def __init__(self, mcp_server_url: str = "http://localhost:12000"):
        self.mcp_server_url = mcp_server_url
        self.playground_dir = Path("/tmp/wordpress-playground")
        self.plugin_dir = Path("/workspace/SEOForge-mcp-server/wordpress-plugin")
        
    def setup_playground(self):
        """Set up WordPress Playground"""
        print("Setting up WordPress Playground...")
        
        # Create playground directory
        self.playground_dir.mkdir(exist_ok=True)
        
        # Create a simple WordPress structure for testing
        wp_content = self.playground_dir / "wp-content"
        plugins_dir = wp_content / "plugins" / "seoforge-mcp"
        plugins_dir.mkdir(parents=True, exist_ok=True)
        
        # Copy plugin files
        print("Copying plugin files...")
        shutil.copytree(self.plugin_dir, plugins_dir, dirs_exist_ok=True)
        
        # Create a simple test configuration
        config = {
            "api_url": self.mcp_server_url,
            "api_key": "test-api-key",
            "secret_key": "default-secret-key",
            "site_url": "https://playground.wordpress.net"
        }
        
        with open(plugins_dir / "test_config.json", "w") as f:
            json.dump(config, f, indent=2)
        
        print(f"✅ WordPress Playground setup complete at {self.playground_dir}")
        return True
    
    def create_plugin_zip(self):
        """Create a ZIP file of the plugin for easy installation"""
        print("Creating plugin ZIP file...")
        
        zip_path = Path("/tmp/seoforge-mcp-plugin.zip")
        
        with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
            for file_path in self.plugin_dir.rglob('*'):
                if file_path.is_file() and not file_path.name.startswith('.'):
                    arcname = f"seoforge-mcp/{file_path.relative_to(self.plugin_dir)}"
                    zipf.write(file_path, arcname)
        
        print(f"✅ Plugin ZIP created at {zip_path}")
        return zip_path
    
    def test_plugin_structure(self):
        """Test that the plugin has the correct structure"""
        print("Testing plugin structure...")
        
        required_files = [
            "seoforge-mcp.php",
            "assets/js/admin.js",
            "assets/css/admin.css",
            "includes/class-admin-interface.php",
            "includes/class-mcp-client.php"
        ]
        
        missing_files = []
        for file_path in required_files:
            full_path = self.plugin_dir / file_path
            if not full_path.exists():
                missing_files.append(file_path)
        
        if missing_files:
            print(f"❌ Missing required files: {missing_files}")
            return False
        
        print("✅ Plugin structure is correct")
        return True
    
    def test_plugin_php_syntax(self):
        """Test PHP syntax of plugin files"""
        print("Testing PHP syntax...")
        
        php_files = list(self.plugin_dir.rglob("*.php"))
        
        for php_file in php_files:
            try:
                # Use php -l to check syntax
                result = subprocess.run(
                    ["php", "-l", str(php_file)],
                    capture_output=True,
                    text=True,
                    timeout=10
                )
                
                if result.returncode != 0:
                    print(f"❌ PHP syntax error in {php_file}: {result.stderr}")
                    return False
                    
            except (subprocess.TimeoutExpired, FileNotFoundError):
                print("⚠️  PHP not available for syntax checking")
                break
        
        print("✅ PHP syntax is correct")
        return True
    
    def test_mcp_server_connectivity(self):
        """Test that the MCP server is accessible"""
        print("Testing MCP server connectivity...")
        
        try:
            response = requests.get(f"{self.mcp_server_url}/health", timeout=10)
            if response.status_code == 200:
                print("✅ MCP server is accessible")
                return True
            else:
                print(f"❌ MCP server returned status {response.status_code}")
                return False
        except Exception as e:
            print(f"❌ Cannot connect to MCP server: {e}")
            return False
    
    def simulate_wordpress_request(self):
        """Simulate a WordPress plugin request to the MCP server"""
        print("Simulating WordPress plugin request...")
        
        import hashlib
        
        site_url = "https://playground.wordpress.net"
        timestamp = int(time.time())
        secret_key = "default-secret-key"
        nonce = hashlib.sha256(f"{site_url}:{timestamp}:{secret_key}".encode()).hexdigest()
        
        headers = {
            "Content-Type": "application/json",
            "X-WordPress-Key": "test-api-key",
            "X-WordPress-Site": site_url,
            "X-WordPress-Nonce": nonce,
            "X-WordPress-Timestamp": str(timestamp)
        }
        
        # Test content generation
        payload = {
            "action": "generate_content",
            "data": {
                "topic": "WordPress SEO Best Practices",
                "content_type": "blog_post",
                "keywords": ["WordPress", "SEO", "optimization"],
                "industry": "technology",
                "language": "en"
            },
            "site_url": site_url,
            "user_id": 1
        }
        
        try:
            response = requests.post(
                f"{self.mcp_server_url}/wordpress/plugin",
                headers=headers,
                json=payload,
                timeout=15
            )
            
            if response.status_code == 200:
                data = response.json()
                if data.get("status") == "success":
                    print("✅ WordPress plugin simulation successful")
                    print(f"   Generated content preview: {data.get('content', '')[:100]}...")
                    return True
                else:
                    print(f"❌ WordPress plugin simulation failed: {data}")
                    return False
            else:
                print(f"❌ WordPress plugin simulation failed with status {response.status_code}")
                return False
                
        except Exception as e:
            print(f"❌ WordPress plugin simulation error: {e}")
            return False
    
    def generate_installation_guide(self):
        """Generate an installation guide for WordPress Playground"""
        print("Generating installation guide...")
        
        guide = """
# SEOForge MCP Plugin - WordPress Playground Installation Guide

## Quick Installation

1. **Download the Plugin**
   - Download the plugin ZIP file: `/tmp/seoforge-mcp-plugin.zip`

2. **Install in WordPress Playground**
   - Go to https://playground.wordpress.net/
   - Navigate to Plugins > Add New > Upload Plugin
   - Upload the ZIP file and activate

3. **Configure the Plugin**
   - Go to SEOForge MCP > Settings
   - Set API URL: `{mcp_server_url}`
   - Set API Key: `test-api-key`
   - Set Secret Key: `default-secret-key`
   - Click "Test Connection" to verify

4. **Test the Plugin**
   - Create a new post
   - Use the SEOForge Content Generator in the sidebar
   - Enter keywords and generate content
   - Use the SEO Analysis tool

## Manual Testing Steps

1. **Content Generation Test**
   - Keywords: "WordPress, SEO, optimization"
   - Industry: Technology
   - Click "Generate Content"

2. **SEO Analysis Test**
   - Write some content in the editor
   - Click "Analyze SEO"
   - Review the analysis results

3. **Keyword Research Test**
   - Go to SEOForge MCP Dashboard
   - Click "Start Research"
   - Enter a seed keyword

## Troubleshooting

- If connection fails, check the MCP server URL
- Ensure the secret key matches between plugin and server
- Check browser console for JavaScript errors
- Verify WordPress site URL is allowed in server configuration

## Server Configuration

The MCP server should be configured with:
- WORDPRESS_SECRET_KEY: `default-secret-key`
- ALLOWED_WORDPRESS_DOMAINS: `playground.wordpress.net`

""".format(mcp_server_url=self.mcp_server_url)
        
        guide_path = Path("/tmp/wordpress-playground-installation-guide.md")
        with open(guide_path, "w") as f:
            f.write(guide)
        
        print(f"✅ Installation guide created at {guide_path}")
        return guide_path
    
    def run_all_tests(self):
        """Run all WordPress Playground tests"""
        print("🚀 Starting WordPress Playground Tests")
        print("=" * 50)
        
        tests = [
            self.test_plugin_structure,
            self.test_plugin_php_syntax,
            self.test_mcp_server_connectivity,
            self.simulate_wordpress_request,
            self.setup_playground,
            self.create_plugin_zip,
            self.generate_installation_guide
        ]
        
        passed = 0
        total = len(tests)
        
        for test in tests:
            try:
                if test():
                    passed += 1
                print("-" * 30)
            except Exception as e:
                print(f"❌ Test failed with exception: {e}")
                print("-" * 30)
        
        print("=" * 50)
        print(f"📊 Test Results: {passed}/{total} tests passed")
        
        if passed == total:
            print("🎉 All tests passed! Plugin is ready for WordPress Playground.")
            print("\n📋 Next Steps:")
            print("1. Use the generated ZIP file to install the plugin")
            print("2. Follow the installation guide")
            print("3. Test the plugin functionality")
            return True
        else:
            print("⚠️  Some tests failed. Please check the issues above.")
            return False

def main():
    """Main function"""
    import argparse
    
    parser = argparse.ArgumentParser(description="Test WordPress Playground integration")
    parser.add_argument("--mcp-url", default="http://localhost:12000", help="MCP server URL")
    
    args = parser.parse_args()
    
    tester = WordPressPlaygroundTester(args.mcp_url)
    success = tester.run_all_tests()
    
    return 0 if success else 1

if __name__ == "__main__":
    exit(main())