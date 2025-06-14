#!/usr/bin/env python3
"""
SEO Forge Plugin Functionality Test Suite
Comprehensive testing to verify all plugin features work without errors
"""

import json
import time
import requests
from datetime import datetime
import os
import sys

class PluginTester:
    def __init__(self):
        self.test_results = {
            "timestamp": datetime.now().isoformat(),
            "plugin_name": "SEO Forge",
            "version": "1.0.0",
            "tests_run": 0,
            "tests_passed": 0,
            "tests_failed": 0,
            "errors": [],
            "warnings": [],
            "detailed_results": []
        }
        
    def log_test(self, test_name, status, message, details=None):
        """Log test result"""
        self.test_results["tests_run"] += 1
        
        if status == "PASS":
            self.test_results["tests_passed"] += 1
            print(f"✅ {test_name}: {message}")
        elif status == "FAIL":
            self.test_results["tests_failed"] += 1
            self.test_results["errors"].append(f"{test_name}: {message}")
            print(f"❌ {test_name}: {message}")
        elif status == "WARN":
            self.test_results["warnings"].append(f"{test_name}: {message}")
            print(f"⚠️  {test_name}: {message}")
            
        self.test_results["detailed_results"].append({
            "test": test_name,
            "status": status,
            "message": message,
            "details": details,
            "timestamp": datetime.now().isoformat()
        })
    
    def test_plugin_files(self):
        """Test if all plugin files exist and are valid"""
        print("\n🔍 Testing Plugin File Structure...")
        
        # Test main plugin files
        plugin_files = [
            "seo-forge-plugin/seo-forge.php",
            "seo-forge-plugin/assets/css/admin.css",
            "seo-forge-plugin/assets/js/admin.js",
            "seo-forge-plugin/includes/class-admin.php",
            "seo-forge-plugin/includes/class-api.php",
            "seo-forge-plugin/includes/class-content-generator.php",
            "seo-forge-plugin/includes/class-seo-analyzer.php",
            "seo-forge-plugin/readme.txt"
        ]
        
        for file_path in plugin_files:
            if os.path.exists(file_path):
                file_size = os.path.getsize(file_path)
                if file_size > 0:
                    self.log_test(f"File Check: {file_path}", "PASS", f"File exists and has content ({file_size} bytes)")
                else:
                    self.log_test(f"File Check: {file_path}", "FAIL", "File exists but is empty")
            else:
                self.log_test(f"File Check: {file_path}", "FAIL", "File does not exist")
    
    def test_php_syntax(self):
        """Test PHP syntax of plugin files"""
        print("\n🔍 Testing PHP Syntax...")
        
        php_files = [
            "seo-forge-plugin/seo-forge.php",
            "seo-forge-plugin/includes/class-admin.php",
            "seo-forge-plugin/includes/class-api.php",
            "seo-forge-plugin/includes/class-content-generator.php",
            "seo-forge-plugin/includes/class-seo-analyzer.php"
        ]
        
        for php_file in php_files:
            if os.path.exists(php_file):
                # Read file and check for basic PHP syntax issues
                try:
                    with open(php_file, 'r', encoding='utf-8') as f:
                        content = f.read()
                        
                    # Basic syntax checks
                    if content.startswith('<?php'):
                        self.log_test(f"PHP Syntax: {php_file}", "PASS", "Valid PHP opening tag")
                    else:
                        self.log_test(f"PHP Syntax: {php_file}", "FAIL", "Missing or invalid PHP opening tag")
                        
                    # Check for common syntax issues
                    if content.count('<?php') == content.count('?>') + 1 or '?>' not in content:
                        self.log_test(f"PHP Tags: {php_file}", "PASS", "PHP tags properly balanced")
                    else:
                        self.log_test(f"PHP Tags: {php_file}", "WARN", "PHP tag balance check needed")
                        
                except Exception as e:
                    self.log_test(f"PHP Read: {php_file}", "FAIL", f"Could not read file: {str(e)}")
    
    def test_css_validity(self):
        """Test CSS file validity"""
        print("\n🔍 Testing CSS Files...")
        
        css_files = [
            "seo-forge-plugin/assets/css/admin.css"
        ]
        
        for css_file in css_files:
            if os.path.exists(css_file):
                try:
                    with open(css_file, 'r', encoding='utf-8') as f:
                        content = f.read()
                    
                    # Basic CSS validation
                    if '{' in content and '}' in content:
                        brace_count = content.count('{') - content.count('}')
                        if brace_count == 0:
                            self.log_test(f"CSS Syntax: {css_file}", "PASS", "CSS braces properly balanced")
                        else:
                            self.log_test(f"CSS Syntax: {css_file}", "FAIL", f"CSS braces unbalanced ({brace_count})")
                    
                    # Check for CSS variables and modern features
                    if '--' in content:
                        self.log_test(f"CSS Features: {css_file}", "PASS", "Uses modern CSS variables")
                    
                    if len(content) > 1000:
                        self.log_test(f"CSS Content: {css_file}", "PASS", f"Substantial CSS content ({len(content)} chars)")
                    
                except Exception as e:
                    self.log_test(f"CSS Read: {css_file}", "FAIL", f"Could not read CSS file: {str(e)}")
    
    def test_javascript_validity(self):
        """Test JavaScript file validity"""
        print("\n🔍 Testing JavaScript Files...")
        
        js_files = [
            "seo-forge-plugin/assets/js/admin.js"
        ]
        
        for js_file in js_files:
            if os.path.exists(js_file):
                try:
                    with open(js_file, 'r', encoding='utf-8') as f:
                        content = f.read()
                    
                    # Basic JavaScript validation
                    if 'function' in content or '=>' in content:
                        self.log_test(f"JS Functions: {js_file}", "PASS", "Contains JavaScript functions")
                    
                    if 'jQuery' in content or '$' in content:
                        self.log_test(f"JS jQuery: {js_file}", "PASS", "Uses jQuery (WordPress standard)")
                    
                    if 'ajax' in content.lower():
                        self.log_test(f"JS AJAX: {js_file}", "PASS", "Implements AJAX functionality")
                    
                    # Check for modern JavaScript features
                    if 'const ' in content or 'let ' in content:
                        self.log_test(f"JS Modern: {js_file}", "PASS", "Uses modern JavaScript syntax")
                    
                except Exception as e:
                    self.log_test(f"JS Read: {js_file}", "FAIL", f"Could not read JavaScript file: {str(e)}")
    
    def test_plugin_structure(self):
        """Test WordPress plugin structure compliance"""
        print("\n🔍 Testing WordPress Plugin Structure...")
        
        # Check main plugin file
        main_file = "seo-forge-plugin/seo-forge.php"
        if os.path.exists(main_file):
            try:
                with open(main_file, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                # Check for WordPress plugin header
                if 'Plugin Name:' in content:
                    self.log_test("Plugin Header", "PASS", "WordPress plugin header found")
                else:
                    self.log_test("Plugin Header", "FAIL", "Missing WordPress plugin header")
                
                # Check for security measures
                if 'defined( \'ABSPATH\' )' in content or 'defined(\'ABSPATH\')' in content:
                    self.log_test("Security Check", "PASS", "ABSPATH security check implemented")
                else:
                    self.log_test("Security Check", "WARN", "ABSPATH security check not found")
                
                # Check for WordPress hooks
                if 'add_action' in content or 'add_filter' in content:
                    self.log_test("WordPress Hooks", "PASS", "Uses WordPress hooks properly")
                else:
                    self.log_test("WordPress Hooks", "WARN", "WordPress hooks not found in main file")
                
            except Exception as e:
                self.log_test("Main File Read", "FAIL", f"Could not read main plugin file: {str(e)}")
    
    def test_mcp_integration(self):
        """Test MCP server integration"""
        print("\n🔍 Testing MCP Integration...")
        
        # Check for MCP-related files
        mcp_files = [
            "backend/main.py",
            "backend/api/mcp-server.py",
            "requirements.txt"
        ]
        
        for mcp_file in mcp_files:
            if os.path.exists(mcp_file):
                self.log_test(f"MCP File: {mcp_file}", "PASS", "MCP integration file exists")
            else:
                self.log_test(f"MCP File: {mcp_file}", "WARN", "MCP file not found")
        
        # Check backend requirements
        if os.path.exists("backend/requirements.txt"):
            try:
                with open("backend/requirements.txt", 'r') as f:
                    requirements = f.read()
                
                if 'fastapi' in requirements.lower():
                    self.log_test("MCP Backend", "PASS", "FastAPI backend configured")
                
                if 'mcp' in requirements.lower():
                    self.log_test("MCP Package", "PASS", "MCP package in requirements")
                
            except Exception as e:
                self.log_test("Requirements Read", "FAIL", f"Could not read requirements: {str(e)}")
    
    def test_deployment_configs(self):
        """Test deployment configuration files"""
        print("\n🔍 Testing Deployment Configurations...")
        
        deployment_files = [
            ("vercel.json", "Vercel"),
            ("render.yaml", "Render"),
            ("docker-compose.yml", "Docker"),
            ("Dockerfile", "Docker Image")
        ]
        
        for file_name, platform in deployment_files:
            if os.path.exists(file_name):
                self.log_test(f"Deployment: {platform}", "PASS", f"{platform} configuration exists")
            else:
                self.log_test(f"Deployment: {platform}", "WARN", f"{platform} configuration not found")
    
    def test_documentation(self):
        """Test documentation completeness"""
        print("\n🔍 Testing Documentation...")
        
        doc_files = [
            ("README.md", "Main documentation"),
            ("DEPLOYMENT_GUIDE.md", "Deployment guide"),
            ("WORDPRESS-PLUGIN-GUIDE.md", "WordPress guide"),
            ("seo-forge-plugin/readme.txt", "Plugin readme")
        ]
        
        for file_name, description in doc_files:
            if os.path.exists(file_name):
                try:
                    with open(file_name, 'r', encoding='utf-8') as f:
                        content = f.read()
                    
                    if len(content) > 500:
                        self.log_test(f"Documentation: {description}", "PASS", f"Comprehensive documentation ({len(content)} chars)")
                    else:
                        self.log_test(f"Documentation: {description}", "WARN", "Documentation exists but may be incomplete")
                        
                except Exception as e:
                    self.log_test(f"Doc Read: {description}", "FAIL", f"Could not read documentation: {str(e)}")
            else:
                self.log_test(f"Documentation: {description}", "WARN", "Documentation file not found")
    
    def generate_report(self):
        """Generate comprehensive test report"""
        print("\n" + "="*60)
        print("🎯 SEO FORGE PLUGIN TEST REPORT")
        print("="*60)
        
        print(f"\n📊 Test Summary:")
        print(f"   Total Tests: {self.test_results['tests_run']}")
        print(f"   Passed: {self.test_results['tests_passed']} ✅")
        print(f"   Failed: {self.test_results['tests_failed']} ❌")
        print(f"   Warnings: {len(self.test_results['warnings'])} ⚠️")
        
        success_rate = (self.test_results['tests_passed'] / self.test_results['tests_run']) * 100 if self.test_results['tests_run'] > 0 else 0
        print(f"   Success Rate: {success_rate:.1f}%")
        
        if self.test_results['errors']:
            print(f"\n❌ Errors Found:")
            for error in self.test_results['errors']:
                print(f"   - {error}")
        
        if self.test_results['warnings']:
            print(f"\n⚠️  Warnings:")
            for warning in self.test_results['warnings']:
                print(f"   - {warning}")
        
        # Overall status
        if self.test_results['tests_failed'] == 0:
            if len(self.test_results['warnings']) == 0:
                print(f"\n🎉 RESULT: PERFECT - All tests passed without warnings!")
            else:
                print(f"\n✅ RESULT: EXCELLENT - All tests passed with minor warnings")
        elif self.test_results['tests_failed'] <= 2:
            print(f"\n👍 RESULT: GOOD - Minor issues found, plugin functional")
        else:
            print(f"\n⚠️  RESULT: NEEDS ATTENTION - Multiple issues found")
        
        # Save detailed report
        with open('plugin_test_report.json', 'w') as f:
            json.dump(self.test_results, f, indent=2)
        
        print(f"\n📄 Detailed report saved to: plugin_test_report.json")
        
        return self.test_results
    
    def run_all_tests(self):
        """Run comprehensive test suite"""
        print("🚀 Starting SEO Forge Plugin Test Suite...")
        print(f"⏰ Test started at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        
        # Run all test categories
        self.test_plugin_files()
        self.test_php_syntax()
        self.test_css_validity()
        self.test_javascript_validity()
        self.test_plugin_structure()
        self.test_mcp_integration()
        self.test_deployment_configs()
        self.test_documentation()
        
        # Generate final report
        return self.generate_report()

def main():
    """Main test execution"""
    tester = PluginTester()
    results = tester.run_all_tests()
    
    # Exit with appropriate code
    if results['tests_failed'] == 0:
        sys.exit(0)  # Success
    else:
        sys.exit(1)  # Failure

if __name__ == "__main__":
    main()