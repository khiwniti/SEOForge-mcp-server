import React from "react";
import { Button } from "@/components/ui/button";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Cog, LayoutDashboard, BarChart2, Wrench, Briefcase, TrendingUp, Users, FileText, HelpCircleIcon, Sparkles } from "lucide-react"; // Added more icons
import { useNavigate, useLocation } from "react-router-dom";

const navItems = [
  { name: "Dashboard", path: "/Dashboard", icon: LayoutDashboard },
  { name: "Projects", path: "/Projects", icon: Briefcase },
  { name: "Analytics", path: "/Analytics", icon: BarChart2 },
  { name: "SEO Tools", path: "/Tools", icon: Wrench }, 
  { name: "Content Hub", path: "/Content", icon: FileText }, 
  { name: "AI Content Wizard", path: "/content-generator-page", icon: Sparkles },
  { name: "Team", path: "/Team", icon: Users }, 
  { name: "Settings", path: "/Settings", icon: Cog },
  { name: "Support", path: "/Support", icon: HelpCircleIcon }, 
];

export default function DashboardPage() {
  const navigate = useNavigate();
  const location = useLocation();

  const user = {
    name: "Valued SEO Pro",
    email: "pro@seoforge.mcp",
    avatarUrl: "https://avatar.vercel.sh/seopro.png", // Using a dynamic avatar service
  };

  // Example: Determine current page for main header
  const currentPage = navItems.find(item => location.pathname.startsWith(item.path))?.name || "Dashboard";

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 text-white flex antialiased">
      {/* Sidebar */}
      <aside className="w-72 bg-gray-800/80 backdrop-blur-md p-5 fixed h-full shadow-2xl flex flex-col border-r border-gray-700">
        <div className="mb-8 flex items-center space-x-3 px-2 py-1">
          {/* Using a more abstract/techy icon for the logo */}
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-10 w-10 text-blue-400">
            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
          </svg>
          <h1 className="text-2xl font-semibold tracking-tight">
            <span className="text-blue-400">SEO</span><span className="text-gray-100">Forge</span>
          </h1>
        </div>
        
        <ScrollArea className="flex-grow -mx-2">
          <nav className="space-y-1.5 px-2">
            {navItems.map((item) => (
              <Button
                key={item.name}
                variant={location.pathname.startsWith(item.path) ? "secondary" : "ghost"}
                className={`w-full justify-start text-left h-11 text-sm font-medium rounded-md group hover:bg-gray-700/60 ${location.pathname.startsWith(item.path) ? 'text-blue-300 bg-gray-700/80' : 'text-gray-300 hover:text-white'}`}
                onClick={() => navigate(item.path)}
              >
                <item.icon className={`mr-3 h-5 w-5 flex-shrink-0 ${location.pathname.startsWith(item.path) ? 'text-blue-300' : 'text-gray-500 group-hover:text-gray-300'}`} />
                {item.name}
              </Button>
            ))}
          </nav>
        </ScrollArea>

        <div className="mt-auto pt-5 border-t border-gray-700/50">
          <div className="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-700/50 cursor-pointer transition-colors">
            <Avatar className="h-10 w-10 border-2 border-gray-600">
              <AvatarImage src={user.avatarUrl} alt={user.name} />
              <AvatarFallback className="bg-blue-500 text-white font-semibold">{user.name.split(' ').map(n=>n[0]).join('').toUpperCase()}</AvatarFallback>
            </Avatar>
            <div>
              <p className="font-semibold text-sm text-gray-100">{user.name}</p>
              <p className="text-xs text-gray-400 group-hover:text-gray-200">{user.email}</p>
            </div>
          </div>
        </div>
      </aside>

      {/* Main Content */}
      <main className="flex-1 ml-72 p-8 md:p-10 overflow-y-auto">
        <header className="mb-10 pb-5 border-b border-gray-700/80 flex justify-between items-center">
          <h2 className="text-3xl md:text-4xl font-bold text-gray-100 tracking-tight">{currentPage}</h2>
          {/* Placeholder for global actions like "Create New Project" */}
          <Button className="bg-blue-500 hover:bg-blue-600 text-white font-semibold shadow-md hover:shadow-lg transition-all">
             <TrendingUp className="mr-2 h-5 w-5" /> New Analysis
          </Button>
        </header>
        
        {/* Conditional rendering based on current page (placeholder) */}
        {location.pathname.startsWith("/Dashboard") && (
          <div className="space-y-8">
            {/* Key Metrics Section */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              {[ 
                { title: "Active Projects", value: "12", change: "+2", icon: Briefcase, color: "text-blue-400", bgColor: "bg-blue-500/10 hover:bg-blue-500/20" },
                { title: "Overall SEO Score", value: "78%", change: "+3%", icon: TrendingUp, color: "text-green-400", bgColor: "bg-green-500/10 hover:bg-green-500/20" },
                { title: "Keywords Tracked", value: "1,250", change: "+50", icon: BarChart2, color: "text-purple-400", bgColor: "bg-purple-500/10 hover:bg-purple-500/20" },
                { title: "Content Pieces", value: "86", change: "+5", icon: FileText, color: "text-orange-400", bgColor: "bg-orange-500/10 hover:bg-orange-500/20" },
              ].map(metric => (
                <Card key={metric.title} className={`bg-gray-800/70 border-gray-700/70 shadow-lg hover:shadow-xl transition-all duration-300 ${metric.bgColor}`}>
                  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle className="text-sm font-medium text-gray-300">{metric.title}</CardTitle>
                    <metric.icon className={`h-5 w-5 ${metric.color}`} />
                  </CardHeader>
                  <CardContent>
                    <div className="text-3xl font-bold text-gray-50">{metric.value}</div>
                    <p className={`text-xs ${metric.change.startsWith('+') ? 'text-green-400' : 'text-red-400'} mt-1`}>{metric.change} this month</p>
                  </CardContent>
                </Card>
              ))}
            </div>

            {/* Chart Placeholder Section */}
            <Card className="bg-gray-800/70 border-gray-700/70 shadow-lg col-span-1 md:col-span-2 lg:col-span-3 xl:col-span-4">
              <CardHeader>
                <CardTitle className="text-xl font-semibold text-gray-200">Performance Overview</CardTitle>
              </CardHeader>
              <CardContent className="h-96 flex items-center justify-center">
                <div className="text-center">
                  <BarChart2 className="h-24 w-24 text-gray-600 mx-auto mb-4" />
                  <p className="text-gray-500 text-lg font-medium">Website Traffic & Engagement Chart</p>
                  <p className="text-gray-600 text-sm">Detailed chart will be implemented here using Recharts.</p>
                </div>
              </CardContent>
            </Card>

            {/* Quick Access / Recent Activity (Placeholder) */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <Card className="bg-gray-800/70 border-gray-700/70 shadow-lg">
                    <CardHeader>
                        <CardTitle className="text-xl font-semibold text-gray-200">Recent Projects</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-gray-500">List of recent projects...</p>
                    </CardContent>
                </Card>
                <Card className="bg-gray-800/70 border-gray-700/70 shadow-lg">
                    <CardHeader>
                        <CardTitle className="text-xl font-semibold text-gray-200">Quick Actions</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2"><Button variant="outline" className="w-full justify-start text-gray-300 border-gray-600 hover:bg-gray-700/70 hover:text-white"><Wrench className="mr-2 h-4 w-4 text-blue-400" /> Full Site Audit</Button><Button variant="outline" className="w-full justify-start text-gray-300 border-gray-600 hover:bg-gray-700/70 hover:text-white"><FileText className="mr-2 h-4 w-4 text-green-400" /> Generate Blog Post</Button><Button variant="outline" className="w-full justify-start text-gray-300 border-gray-600 hover:bg-gray-700/70 hover:text-white"><BarChart2 className="mr-2 h-4 w-4 text-purple-400" /> Track New Keywords</Button></div>
                    </CardContent>
                </Card>
            </div>

          </div>
        )}

        {/* Placeholder for other page content e.g. Projects, Analytics etc. */}
        {!location.pathname.startsWith("/Dashboard") && (
            <div className="text-center py-20">
                <h3 className="text-2xl font-semibold text-gray-300">Welcome to {currentPage}</h3>
                <p className="text-gray-500">Content for this section will be built soon.</p>
            </div>
        )}

      </main>
    </div>
  );
}
