import React, { useState, useEffect } from "react";
import { backend } from "app";

// API configuration for different environments
const API_BASE_URL = process.env.NODE_ENV === 'production'
  ? (process.env.REACT_APP_API_URL || '/api')
  : 'http://localhost:8000/api';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { 
  Activity, 
  Server, 
  Zap, 
  BarChart3, 
  Clock, 
  CheckCircle, 
  AlertCircle,
  TrendingUp,
  Users,
  Globe,
  Cpu,
  Database,
  Network
} from "lucide-react";

interface MCPServerStatus {
  status: string;
  version: string;
  available_tools: string[];
  supported_industries: string[];
  active_connections: number;
  uptime: string;
}

interface DashboardStats {
  totalRequests: number;
  successfulRequests: number;
  avgResponseTime: number;
  topTools: Array<{ name: string; count: number }>;
  industryUsage: Array<{ industry: string; count: number }>;
}

interface ToolExecution {
  tool_name: string;
  success: boolean;
  execution_time: number;
  timestamp: string;
  result: any;
}

export default function MCPDashboard() {
  const [mcpStatus, setMcpStatus] = useState<MCPServerStatus | null>(null);
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedIndustry, setSelectedIndustry] = useState("general");
  const [recentExecutions, setRecentExecutions] = useState<ToolExecution[]>([]);

  useEffect(() => {
    loadDashboardData();
    const interval = setInterval(loadDashboardData, 30000); // Refresh every 30 seconds
    return () => clearInterval(interval);
  }, []);

  const loadDashboardData = async () => {
    try {
      setLoading(true);
      
      // Load MCP server status
      const statusResponse = await backend.mcpServer.mcpServerStatusGet();
      if (statusResponse.data) {
        setMcpStatus(statusResponse.data);
      }

      // Simulate dashboard stats
      setStats({
        totalRequests: 1247,
        successfulRequests: 1198,
        avgResponseTime: 1.2,
        topTools: [
          { name: "content_generation", count: 456 },
          { name: "seo_analysis", count: 342 },
          { name: "keyword_research", count: 289 },
          { name: "industry_analysis", count: 160 }
        ],
        industryUsage: [
          { industry: "ecommerce", count: 387 },
          { industry: "healthcare", count: 245 },
          { industry: "technology", count: 198 },
          { industry: "finance", count: 156 },
          { industry: "general", count: 261 }
        ]
      });

      // Simulate recent executions
      setRecentExecutions([
        {
          tool_name: "content_generation",
          success: true,
          execution_time: 2.1,
          timestamp: new Date(Date.now() - 5000).toISOString(),
          result: { content_type: "blog_post", word_count: 1200 }
        },
        {
          tool_name: "seo_analysis",
          success: true,
          execution_time: 0.8,
          timestamp: new Date(Date.now() - 15000).toISOString(),
          result: { overall_score: 85 }
        },
        {
          tool_name: "keyword_research",
          success: false,
          execution_time: 0.3,
          timestamp: new Date(Date.now() - 25000).toISOString(),
          result: { error: "Rate limit exceeded" }
        }
      ]);

    } catch (error) {
      console.error("Failed to load dashboard data:", error);
    } finally {
      setLoading(false);
    }
  };

  const executeQuickTool = async (toolName: string, parameters: any = {}) => {
    try {
      const response = await backend.mcpServer.mcpServerExecuteToolPost({
        tool_name: toolName,
        parameters,
        context: { industry: selectedIndustry },
        industry: selectedIndustry
      });

      if (response.data) {
        // Add to recent executions
        const newExecution: ToolExecution = {
          tool_name: toolName,
          success: response.data.success,
          execution_time: response.data.execution_time,
          timestamp: response.data.timestamp,
          result: response.data.result
        };
        setRecentExecutions(prev => [newExecution, ...prev.slice(0, 9)]);
      }
    } catch (error) {
      console.error("Failed to execute tool:", error);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading MCP Dashboard...</p>
        </div>
      </div>
    );
  }

  const successRate = stats ? Math.round((stats.successfulRequests / stats.totalRequests) * 100) : 0;

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="mb-8">
          <div className="flex justify-between items-center">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">Universal MCP Server Dashboard</h1>
              <p className="mt-2 text-gray-600">
                Monitor and manage your Model Context Protocol server
              </p>
            </div>
            <div className="flex items-center space-x-4">
              <Select value={selectedIndustry} onValueChange={setSelectedIndustry}>
                <SelectTrigger className="w-48">
                  <SelectValue placeholder="Select Industry" />
                </SelectTrigger>
                <SelectContent>
                  {mcpStatus?.supported_industries.map((industry) => (
                    <SelectItem key={industry} value={industry}>
                      {industry.charAt(0).toUpperCase() + industry.slice(1)}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <Badge variant={mcpStatus?.status === 'active' ? 'default' : 'destructive'}>
                <Server className="w-3 h-3 mr-1" />
                {mcpStatus?.status === 'active' ? 'Online' : 'Offline'}
              </Badge>
            </div>
          </div>
        </div>

        {/* Server Status Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Server Status</CardTitle>
              <Activity className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                {mcpStatus?.status === 'active' ? 'Active' : 'Inactive'}
              </div>
              <p className="text-xs text-muted-foreground">
                Uptime: {mcpStatus?.uptime || 'N/A'}
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Requests</CardTitle>
              <BarChart3 className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats?.totalRequests.toLocaleString()}</div>
              <p className="text-xs text-muted-foreground">
                +12% from last hour
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Success Rate</CardTitle>
              <CheckCircle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{successRate}%</div>
              <p className="text-xs text-muted-foreground">
                {stats?.successfulRequests} successful
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Avg Response Time</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats?.avgResponseTime}s</div>
              <p className="text-xs text-muted-foreground">
                -0.2s from last hour
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Main Content Tabs */}
        <Tabs defaultValue="overview" className="space-y-6">
          <TabsList>
            <TabsTrigger value="overview">Overview</TabsTrigger>
            <TabsTrigger value="tools">Tools</TabsTrigger>
            <TabsTrigger value="analytics">Analytics</TabsTrigger>
            <TabsTrigger value="monitoring">Monitoring</TabsTrigger>
          </TabsList>

          <TabsContent value="overview" className="space-y-6">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              {/* Quick Actions */}
              <Card>
                <CardHeader>
                  <CardTitle>Quick Actions</CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                  <Button 
                    className="w-full justify-start" 
                    variant="outline"
                    onClick={() => executeQuickTool("content_generation", { 
                      topic: "AI in modern business", 
                      content_type: "blog_post" 
                    })}
                  >
                    <Zap className="mr-2 h-4 w-4" />
                    Generate Sample Content
                  </Button>
                  <Button 
                    className="w-full justify-start" 
                    variant="outline"
                    onClick={() => executeQuickTool("seo_analysis", { 
                      url: "https://example.com" 
                    })}
                  >
                    <TrendingUp className="mr-2 h-4 w-4" />
                    Run SEO Analysis
                  </Button>
                  <Button 
                    className="w-full justify-start" 
                    variant="outline"
                    onClick={() => executeQuickTool("keyword_research", { 
                      seed_keyword: "artificial intelligence" 
                    })}
                  >
                    <Globe className="mr-2 h-4 w-4" />
                    Research Keywords
                  </Button>
                  <Button 
                    className="w-full justify-start" 
                    variant="outline"
                    onClick={() => executeQuickTool("industry_analysis", { 
                      industry: selectedIndustry 
                    })}
                  >
                    <BarChart3 className="mr-2 h-4 w-4" />
                    Analyze Industry
                  </Button>
                </CardContent>
              </Card>

              {/* Recent Executions */}
              <Card>
                <CardHeader>
                  <CardTitle>Recent Tool Executions</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    {recentExecutions.map((execution, index) => (
                      <div key={index} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div className="flex items-center space-x-3">
                          {execution.success ? (
                            <CheckCircle className="h-4 w-4 text-green-500" />
                          ) : (
                            <AlertCircle className="h-4 w-4 text-red-500" />
                          )}
                          <div>
                            <p className="text-sm font-medium">{execution.tool_name}</p>
                            <p className="text-xs text-gray-500">
                              {new Date(execution.timestamp).toLocaleTimeString()}
                            </p>
                          </div>
                        </div>
                        <div className="text-right">
                          <p className="text-sm font-medium">{execution.execution_time}s</p>
                          <Badge variant={execution.success ? "default" : "destructive"} className="text-xs">
                            {execution.success ? "Success" : "Failed"}
                          </Badge>
                        </div>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Top Tools and Industry Usage */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <Card>
                <CardHeader>
                  <CardTitle>Most Used Tools</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    {stats?.topTools.map((tool, index) => (
                      <div key={tool.name} className="flex items-center justify-between">
                        <div className="flex items-center space-x-3">
                          <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <span className="text-sm font-medium text-blue-600">{index + 1}</span>
                          </div>
                          <span className="font-medium">{tool.name}</span>
                        </div>
                        <span className="text-sm text-gray-500">{tool.count} uses</span>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Industry Usage</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    {stats?.industryUsage.map((industry) => (
                      <div key={industry.industry} className="flex items-center justify-between">
                        <span className="font-medium capitalize">{industry.industry}</span>
                        <div className="flex items-center space-x-2">
                          <div className="w-20 bg-gray-200 rounded-full h-2">
                            <div 
                              className="bg-blue-600 h-2 rounded-full" 
                              style={{ width: `${(industry.count / 400) * 100}%` }}
                            ></div>
                          </div>
                          <span className="text-sm text-gray-500">{industry.count}</span>
                        </div>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          <TabsContent value="tools">
            <Card>
              <CardHeader>
                <CardTitle>Available MCP Tools</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  {mcpStatus?.available_tools.map((tool) => (
                    <div key={tool} className="p-4 border rounded-lg hover:bg-gray-50 cursor-pointer">
                      <div className="flex items-center space-x-3">
                        <Cpu className="h-5 w-5 text-blue-500" />
                        <div>
                          <h3 className="font-medium">{tool}</h3>
                          <p className="text-sm text-gray-500">MCP Tool</p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="analytics">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <Card>
                <CardHeader>
                  <CardTitle>Performance Metrics</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    <div className="flex justify-between items-center">
                      <span>Average Response Time</span>
                      <span className="font-medium">{stats?.avgResponseTime}s</span>
                    </div>
                    <div className="flex justify-between items-center">
                      <span>Success Rate</span>
                      <span className="font-medium text-green-600">{successRate}%</span>
                    </div>
                    <div className="flex justify-between items-center">
                      <span>Total Requests</span>
                      <span className="font-medium">{stats?.totalRequests.toLocaleString()}</span>
                    </div>
                    <div className="flex justify-between items-center">
                      <span>Active Connections</span>
                      <span className="font-medium">{mcpStatus?.active_connections}</span>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>System Information</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    <div className="flex justify-between items-center">
                      <span>Server Version</span>
                      <span className="font-medium">{mcpStatus?.version}</span>
                    </div>
                    <div className="flex justify-between items-center">
                      <span>Supported Industries</span>
                      <span className="font-medium">{mcpStatus?.supported_industries.length}</span>
                    </div>
                    <div className="flex justify-between items-center">
                      <span>Available Tools</span>
                      <span className="font-medium">{mcpStatus?.available_tools.length}</span>
                    </div>
                    <div className="flex justify-between items-center">
                      <span>Uptime</span>
                      <span className="font-medium">{mcpStatus?.uptime}</span>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          <TabsContent value="monitoring">
            <Card>
              <CardHeader>
                <CardTitle>Real-time Monitoring</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <div className="text-center">
                    <Database className="h-12 w-12 text-blue-500 mx-auto mb-2" />
                    <h3 className="font-medium">Database</h3>
                    <p className="text-sm text-green-600">Connected</p>
                  </div>
                  <div className="text-center">
                    <Network className="h-12 w-12 text-green-500 mx-auto mb-2" />
                    <h3 className="font-medium">Network</h3>
                    <p className="text-sm text-green-600">Healthy</p>
                  </div>
                  <div className="text-center">
                    <Users className="h-12 w-12 text-purple-500 mx-auto mb-2" />
                    <h3 className="font-medium">Active Users</h3>
                    <p className="text-sm text-gray-600">{mcpStatus?.active_connections}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
}
