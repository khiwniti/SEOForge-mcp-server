import React from "react";
import { Button } from "@/components/ui/button";

export default function App() {
  return (
    <div className="min-h-screen bg-gray-900 text-white flex flex-col">
      {/* Header/Navigation */}
      <header className="py-6 px-4 sm:px-6 lg:px-8 border-b border-gray-700 sticky top-0 z-50 bg-gray-900 bg-opacity-80 backdrop-blur-md">
        <div className="container mx-auto flex justify-between items-center">
          <h1 className="text-3xl font-bold">
            <span className="text-blue-400">SEO</span><span className="text-gray-100">Forge</span> <span className="text-sm font-light text-gray-400">MCP</span>
          </h1>
          <nav className="space-x-2">
            <Button variant="ghost" className="hover:bg-gray-700 hover:text-white">Login</Button>
            <Button className="bg-blue-500 hover:bg-blue-600 text-white">Get Started</Button>
          </nav>
        </div>
      </header>

      {/* Hero Section */}
      <main className="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 flex flex-col items-center text-center">
        <h2 className="text-5xl sm:text-6xl lg:text-7xl font-extrabold mb-8 leading-tight">
          <span className="block">Unleash Peak SEO</span>
          <span className="block text-blue-400">Performance with AI</span>
        </h2>
        <p className="text-lg sm:text-xl lg:text-2xl text-gray-300 mb-12 max-w-3xl">
          SEOForge MCP empowers SEO professionals with a cutting-edge suite of AI-driven tools for content generation, optimization, and analytics. Connect your WordPress sites and elevate your strategy to new heights.
        </p>
        <div className="space-x-0 space-y-4 sm:space-y-0 sm:space-x-4 flex flex-col sm:flex-row">
          <Button size="lg" className="bg-blue-500 hover:bg-blue-600 text-lg px-10 py-4 shadow-lg transform hover:scale-105 transition-transform duration-150">
            Start Optimizing Now
          </Button>
          <Button variant="outline" size="lg" className="text-lg px-10 py-4 border-blue-400 text-blue-400 hover:bg-blue-400 hover:text-gray-900 shadow-lg transform hover:scale-105 transition-transform duration-150">
            Learn More
          </Button>
        </div>
      </main>

      {/* Feature Highlights Section */}
      <section className="py-20 md:py-28 bg-gray-800">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h3 className="text-4xl sm:text-5xl font-bold mb-16 text-gray-100">Why SEOForge MCP?</h3>
          <div className="grid md:grid-cols-3 gap-8 lg:gap-12">
            {/* Feature 1 */}
            <div className="p-8 bg-gray-700 rounded-xl shadow-2xl flex flex-col items-center transform hover:scale-105 transition-transform duration-300 ease-in-out">
              <div className="text-6xl mb-6 text-blue-400">🔗</div> {/* Using a different icon for connection */}
              <h4 className="text-2xl font-semibold mb-4 text-blue-300">Seamless WordPress Integration</h4>
              <p className="text-gray-300 text-center px-2 leading-relaxed">
                Connect multiple WordPress sites effortlessly. Our MCP server acts as a central hub, allowing you to manage SEO tasks, deploy content, and track performance across all your connected blogs via a secure and intuitive interface.
              </p>
            </div>
            {/* Feature 2 */}
            <div className="p-8 bg-gray-700 rounded-xl shadow-2xl flex flex-col items-center transform hover:scale-105 transition-transform duration-300 ease-in-out">
              <div className="text-6xl mb-6 text-green-400">💡</div> {/* Using a different icon for AI/ideas */}
              <h4 className="text-2xl font-semibold mb-4 text-green-300">AI Blog & Image Generation</h4>
              <p className="text-gray-300 text-center px-2 leading-relaxed">
                Leverage state-of-the-art AI to produce engaging, SEO-friendly blog posts and articles. Our platform helps you overcome writer's block and scale your content production, complete with AI-generated images to enhance visual appeal.
              </p>
            </div>
            {/* Feature 3 */}
            <div className="p-8 bg-gray-700 rounded-xl shadow-2xl flex flex-col items-center transform hover:scale-105 transition-transform duration-300 ease-in-out">
              <div className="text-6xl mb-6 text-purple-400">📈</div> {/* Using a different icon for analytics */}
              <h4 className="text-2xl font-semibold mb-4 text-purple-300">Advanced SEO Analytics</h4>
              <p className="text-gray-300 text-center px-2 leading-relaxed">
                Gain actionable insights with our comprehensive SEO analytics. Track keyword rankings, analyze content performance, and receive AI-powered recommendations to continuously improve your site's visibility and authority.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="py-10 px-4 sm:px-6 lg:px-8 border-t border-gray-700 text-center bg-gray-900">
        <p className="text-gray-400">&copy; {new Date().getFullYear()} SEOForge MCP. Powering the next generation of SEO.</p>
        <p className="text-sm text-gray-500 mt-2">Built with passion by AI for professionals.</p>
      </footer>
    </div>
  );
}
