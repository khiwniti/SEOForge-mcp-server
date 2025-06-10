import React, { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { toast } from "sonner";
import brain from "brain";
import { Sparkles, Loader2 } from "lucide-react";

export default function ContentGeneratorPage() {
  const [topic, setTopic] = useState("");
  const [keywords, setKeywords] = useState(""); // Comma-separated string
  const [generatedContent, setGeneratedContent] = useState("");
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!topic.trim()) {
      toast.error("Blog topic cannot be empty.");
      return;
    }

    setIsLoading(true);
    setGeneratedContent("");
    const toastId = toast.loading("Generating AI Blog Content...", {
      description: `Topic: ${topic}`,
    });

    try {
      const keywordsArray = keywords.split(',').map(kw => kw.trim()).filter(kw => kw);
      
      // NOTE: The actual method name on `brain` might be different, e.g., brain.blogGeneratorGenerate
      // This will be verified once the client is regenerated and inspected.
      // For now, assuming a direct mapping based on the endpoint path.
      const response = await brain.generate_blog_content({ topic, keywords: keywordsArray.length > 0 ? keywordsArray : undefined });
      const data = await response.json();

      if (response.ok) {
        setGeneratedContent(data.generated_text);
        toast.success("AI Content Generated Successfully!", {
          id: toastId,
          description: "Review the content below."
        });
      } else {
        const errorData = data as { detail?: string };
        throw new Error(errorData.detail || "Failed to generate content from API");
      }
    } catch (error: any) {
      console.error("Content generation error:", error);
      toast.error("Content Generation Failed", {
        id: toastId,
        description: error.message || "An unexpected error occurred.",
      });
    }
    setIsLoading(false);
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 text-white p-6 md:p-10">
      <header className="mb-8 pb-4 border-b border-gray-700/80">
        <h1 className="text-3xl md:text-4xl font-bold text-gray-100 tracking-tight flex items-center">
          <Sparkles className="mr-3 h-8 w-8 text-purple-400" /> AI Blog Content Generator
        </h1>
        <p className="text-gray-400 mt-1">Craft compelling, SEO-optimized blog posts with the power of AI.</p>
      </header>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="md:col-span-1 space-y-6">
          <Card className="bg-gray-800/70 border-gray-700/70 shadow-xl">
            <CardHeader>
              <CardTitle className="text-xl font-semibold text-gray-200">Content Parameters</CardTitle>
              <CardDescription className="text-gray-400">Provide a topic and optional keywords.</CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <Label htmlFor="topic" className="text-gray-300 font-medium">Blog Topic / Title</Label>
                  <Input
                    id="topic"
                    value={topic}
                    onChange={(e) => setTopic(e.target.value)}
                    placeholder="e.g., The Future of AI in SEO"
                    className="bg-gray-700 border-gray-600 text-white focus:border-purple-500 mt-1"
                    required
                  />
                </div>
                <div>
                  <Label htmlFor="keywords" className="text-gray-300 font-medium">Keywords (comma-separated)</Label>
                  <Input
                    id="keywords"
                    value={keywords}
                    onChange={(e) => setKeywords(e.target.value)}
                    placeholder="e.g., AI, SEO, content marketing"
                    className="bg-gray-700 border-gray-600 text-white focus:border-purple-500 mt-1"
                  />
                </div>
                <Button type="submit" className="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3" disabled={isLoading}>
                  {isLoading ? (
                    <><Loader2 className="mr-2 h-5 w-5 animate-spin" /> Generating...</>
                  ) : (
                    <><Sparkles className="mr-2 h-5 w-5" /> Generate Content</>
                  )}
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>

        <div className="md:col-span-2">
          <Card className="bg-gray-800/70 border-gray-700/70 shadow-xl min-h-[400px]">
            <CardHeader>
              <CardTitle className="text-xl font-semibold text-gray-200">Generated Content</CardTitle>
              <CardDescription className="text-gray-400">Review and copy the AI-generated blog post below. Editing features coming soon!</CardDescription>
            </CardHeader>
            <CardContent>
              {isLoading && !generatedContent && (
                <div className="flex flex-col items-center justify-center h-60 text-gray-500">
                  <Loader2 className="h-12 w-12 animate-spin text-purple-400 mb-4" />
                  <p className="text-lg">Generating your content... please wait.</p>
                </div>
              )}
              {!isLoading && !generatedContent && (
                 <div className="flex flex-col items-center justify-center h-60 text-gray-500">
                  <Sparkles className="h-12 w-12 text-gray-600 mb-4" />
                  <p className="text-lg">Your generated content will appear here.</p>
                </div>
              )}
              {generatedContent && (
                <Textarea
                  value={generatedContent}
                  readOnly // For now, will add editor later
                  className="w-full h-[500px] bg-gray-700/50 border-gray-600 text-gray-200 p-4 rounded-md prose prose-invert max-w-none"
                  placeholder="Generated content will appear here..."
                />
                // TODO: Add copy to clipboard button
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
