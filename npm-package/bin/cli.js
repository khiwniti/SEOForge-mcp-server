#!/usr/bin/env node

const { Command } = require('commander');
const chalk = require('chalk');
const SEOForgeClient = require('../lib/client');

const program = new Command();
const client = new SEOForgeClient();

program
  .name('seo-forge')
  .description('🚀 SEOForge Universal MCP Server CLI - AI-powered content generation, SEO analysis, and chatbot')
  .version('1.0.0');

// Status command
program
  .command('status')
  .description('Check MCP server status')
  .option('-s, --server <url>', 'Server URL', 'https://seoforge-mcp-server.onrender.com')
  .action(async (options) => {
    try {
      console.log(chalk.blue('🔍 Checking server status...'));
      const status = await client.getStatus(options.server);
      
      console.log(chalk.green('✅ Server is online!'));
      console.log(chalk.cyan('📊 Server Info:'));
      console.log(`   Version: ${status.version}`);
      console.log(`   Status: ${status.status}`);
      console.log(`   Features: ${status.features?.join(', ') || 'N/A'}`);
      console.log(`   Timestamp: ${status.timestamp}`);
    } catch (error) {
      console.log(chalk.red('❌ Server is offline or unreachable'));
      console.log(chalk.red(`Error: ${error.message}`));
      process.exit(1);
    }
  });

// Generate content command
program
  .command('generate')
  .description('Generate AI content')
  .requiredOption('-t, --topic <topic>', 'Content topic')
  .option('-k, --keywords <keywords>', 'Keywords (comma-separated)', '')
  .option('-l, --language <language>', 'Language', 'en')
  .option('-s, --server <url>', 'Server URL', 'https://seoforge-mcp-server.onrender.com')
  .option('--tone <tone>', 'Content tone', 'professional')
  .option('--length <length>', 'Content length', 'medium')
  .option('--type <type>', 'Content type', 'blog_post')
  .action(async (options) => {
    try {
      console.log(chalk.blue(`✍️ Generating content about "${options.topic}"...`));
      
      const keywords = options.keywords ? options.keywords.split(',').map(k => k.trim()) : [];
      const result = await client.generateContent(options.server, {
        topic: options.topic,
        keywords: keywords,
        language: options.language,
        tone: options.tone,
        length: options.length,
        content_type: options.type
      });
      
      console.log(chalk.green('✅ Content generated successfully!'));
      console.log(chalk.cyan('\n📝 Generated Content:'));
      console.log(chalk.white('━'.repeat(60)));
      console.log(chalk.bold.yellow(`Title: ${result.content.title}`));
      console.log(chalk.white('━'.repeat(60)));
      console.log(result.content.body);
      console.log(chalk.white('━'.repeat(60)));
      console.log(chalk.gray(`📊 Stats: ${result.content.word_count} words, ${result.content.reading_time} min read`));
      console.log(chalk.gray(`🏷️ Keywords: ${result.content.keywords.join(', ')}`));
      console.log(chalk.gray(`📄 Meta: ${result.content.meta_description}`));
    } catch (error) {
      console.log(chalk.red('❌ Content generation failed'));
      console.log(chalk.red(`Error: ${error.message}`));
      process.exit(1);
    }
  });

// Analyze SEO command
program
  .command('analyze')
  .description('Analyze content for SEO')
  .requiredOption('-c, --content <content>', 'Content to analyze')
  .option('-k, --keywords <keywords>', 'Target keywords (comma-separated)', '')
  .option('-l, --language <language>', 'Language', 'en')
  .option('-s, --server <url>', 'Server URL', 'https://seoforge-mcp-server.onrender.com')
  .action(async (options) => {
    try {
      console.log(chalk.blue('📊 Analyzing content for SEO...'));
      
      const keywords = options.keywords ? options.keywords.split(',').map(k => k.trim()) : [];
      const result = await client.analyzeSEO(options.server, {
        content: options.content,
        keywords: keywords,
        language: options.language
      });
      
      console.log(chalk.green('✅ SEO analysis completed!'));
      console.log(chalk.cyan('\n📈 SEO Analysis Results:'));
      console.log(chalk.white('━'.repeat(60)));
      console.log(chalk.bold.yellow(`SEO Score: ${result.analysis.seo_score}/100`));
      console.log(chalk.bold.blue(`Word Count: ${result.analysis.word_count}`));
      console.log(chalk.bold.green(`Readability: ${result.analysis.readability_score}/100`));
      
      if (result.analysis.keyword_analysis) {
        console.log(chalk.cyan('\n🏷️ Keyword Analysis:'));
        Object.entries(result.analysis.keyword_analysis).forEach(([keyword, density]) => {
          console.log(`   ${keyword}: ${density.toFixed(2)}%`);
        });
      }
      
      console.log(chalk.cyan('\n💡 Recommendations:'));
      result.analysis.recommendations.forEach((rec, index) => {
        console.log(chalk.yellow(`   ${index + 1}. ${rec}`));
      });
      
      console.log(chalk.white('━'.repeat(60)));
    } catch (error) {
      console.log(chalk.red('❌ SEO analysis failed'));
      console.log(chalk.red(`Error: ${error.message}`));
      process.exit(1);
    }
  });

// Chat command
program
  .command('chat')
  .description('Chat with AI assistant')
  .requiredOption('-m, --message <message>', 'Message to send')
  .option('-w, --website <url>', 'Website URL for context')
  .option('-s, --server <url>', 'Server URL', 'https://seoforge-mcp-server.onrender.com')
  .action(async (options) => {
    try {
      console.log(chalk.blue('💬 Chatting with AI assistant...'));
      
      const result = await client.chat(options.server, {
        message: options.message,
        website_url: options.website
      });
      
      console.log(chalk.green('✅ Response received!'));
      console.log(chalk.cyan('\n🤖 AI Assistant:'));
      console.log(chalk.white('━'.repeat(60)));
      console.log(result.response.text);
      console.log(chalk.white('━'.repeat(60)));
      
      if (result.response.suggestions && result.response.suggestions.length > 0) {
        console.log(chalk.cyan('\n💡 Suggestions:'));
        result.response.suggestions.forEach((suggestion, index) => {
          console.log(chalk.yellow(`   ${index + 1}. ${suggestion.text}`));
        });
      }
    } catch (error) {
      console.log(chalk.red('❌ Chat failed'));
      console.log(chalk.red(`Error: ${error.message}`));
      process.exit(1);
    }
  });

// Image generation command
program
  .command('image')
  .description('Generate AI image')
  .requiredOption('-p, --prompt <prompt>', 'Image prompt')
  .option('-s, --server <url>', 'Server URL', 'https://seoforge-mcp-server.onrender.com')
  .option('--style <style>', 'Image style', 'professional')
  .option('--size <size>', 'Image size', '1024x1024')
  .action(async (options) => {
    try {
      console.log(chalk.blue(`🎨 Generating image: "${options.prompt}"...`));
      
      const result = await client.generateImage(options.server, {
        prompt: options.prompt,
        style: options.style,
        size: options.size
      });
      
      console.log(chalk.green('✅ Image generated successfully!'));
      console.log(chalk.cyan('\n🖼️ Generated Image:'));
      console.log(chalk.white('━'.repeat(60)));
      console.log(chalk.bold.yellow(`URL: ${result.image.url}`));
      console.log(chalk.gray(`Prompt: ${result.image.prompt}`));
      console.log(chalk.gray(`Style: ${result.image.style}`));
      console.log(chalk.gray(`Size: ${result.image.size}`));
      console.log(chalk.white('━'.repeat(60)));
      console.log(chalk.blue('💡 Copy the URL above to view your generated image!'));
    } catch (error) {
      console.log(chalk.red('❌ Image generation failed'));
      console.log(chalk.red(`Error: ${error.message}`));
      process.exit(1);
    }
  });

// Blog with images command
program
  .command('blog')
  .description('Generate complete blog post with images')
  .requiredOption('-t, --topic <topic>', 'Blog topic')
  .option('-k, --keywords <keywords>', 'Keywords (comma-separated)', '')
  .option('-l, --language <language>', 'Language', 'en')
  .option('-s, --server <url>', 'Server URL', 'https://seoforge-mcp-server.onrender.com')
  .option('--images <count>', 'Number of images', '2')
  .action(async (options) => {
    try {
      console.log(chalk.blue(`📝 Generating complete blog post about "${options.topic}"...`));
      
      const keywords = options.keywords ? options.keywords.split(',').map(k => k.trim()) : [];
      const result = await client.generateBlog(options.server, {
        topic: options.topic,
        keywords: keywords,
        language: options.language,
        include_images: true,
        image_count: parseInt(options.images)
      });
      
      console.log(chalk.green('✅ Blog post generated successfully!'));
      console.log(chalk.cyan('\n📰 Complete Blog Post:'));
      console.log(chalk.white('━'.repeat(60)));
      console.log(chalk.bold.yellow(`Title: ${result.blog.title}`));
      console.log(chalk.white('━'.repeat(60)));
      console.log(result.blog.content);
      console.log(chalk.white('━'.repeat(60)));
      
      if (result.blog.images && result.blog.images.length > 0) {
        console.log(chalk.cyan('\n🖼️ Generated Images:'));
        result.blog.images.forEach((image, index) => {
          console.log(chalk.yellow(`   ${index + 1}. ${image.url}`));
          console.log(chalk.gray(`      Alt: ${image.alt}`));
        });
      }
      
      console.log(chalk.gray(`\n📊 Stats: ${result.blog.word_count} words, ${result.blog.reading_time} min read`));
      console.log(chalk.gray(`🏷️ Keywords: ${result.blog.keywords.join(', ')}`));
    } catch (error) {
      console.log(chalk.red('❌ Blog generation failed'));
      console.log(chalk.red(`Error: ${error.message}`));
      process.exit(1);
    }
  });

// Interactive mode
program
  .command('interactive')
  .alias('i')
  .description('Start interactive mode')
  .option('-s, --server <url>', 'Server URL', 'https://seoforge-mcp-server.onrender.com')
  .action(async (options) => {
    const inquirer = require('inquirer');
    
    console.log(chalk.blue('🚀 Welcome to SEOForge Interactive Mode!'));
    console.log(chalk.gray('Use this mode to easily access all features.\n'));
    
    try {
      const answers = await inquirer.prompt([
        {
          type: 'list',
          name: 'action',
          message: 'What would you like to do?',
          choices: [
            { name: '📊 Check server status', value: 'status' },
            { name: '✍️ Generate content', value: 'generate' },
            { name: '📈 Analyze SEO', value: 'analyze' },
            { name: '💬 Chat with AI', value: 'chat' },
            { name: '🎨 Generate image', value: 'image' },
            { name: '📝 Create blog post', value: 'blog' },
            { name: '❌ Exit', value: 'exit' }
          ]
        }
      ]);
      
      if (answers.action === 'exit') {
        console.log(chalk.blue('👋 Goodbye!'));
        return;
      }
      
      // Handle each action with additional prompts
      await handleInteractiveAction(answers.action, options.server);
      
    } catch (error) {
      console.log(chalk.red('❌ Interactive mode failed'));
      console.log(chalk.red(`Error: ${error.message}`));
    }
  });

async function handleInteractiveAction(action, serverUrl) {
  const inquirer = require('inquirer');
  
  switch (action) {
    case 'status':
      try {
        const status = await client.getStatus(serverUrl);
        console.log(chalk.green('\n✅ Server is online!'));
        console.log(`Version: ${status.version}`);
        console.log(`Features: ${status.features?.join(', ') || 'N/A'}`);
      } catch (error) {
        console.log(chalk.red('\n❌ Server is offline'));
      }
      break;
      
    case 'generate':
      const genAnswers = await inquirer.prompt([
        { type: 'input', name: 'topic', message: 'Enter topic:', validate: input => input.length > 0 },
        { type: 'input', name: 'keywords', message: 'Enter keywords (comma-separated):' },
        { type: 'list', name: 'language', message: 'Select language:', choices: ['en', 'th', 'es', 'fr', 'de'] }
      ]);
      
      try {
        const keywords = genAnswers.keywords ? genAnswers.keywords.split(',').map(k => k.trim()) : [];
        const result = await client.generateContent(serverUrl, {
          topic: genAnswers.topic,
          keywords: keywords,
          language: genAnswers.language
        });
        
        console.log(chalk.green('\n✅ Content generated!'));
        console.log(chalk.bold.yellow(`Title: ${result.content.title}`));
        console.log(result.content.body);
      } catch (error) {
        console.log(chalk.red('\n❌ Generation failed'));
      }
      break;
      
    // Add other cases as needed...
  }
}

// Help command
program
  .command('help')
  .description('Show detailed help and examples')
  .action(() => {
    console.log(chalk.blue('🚀 SEOForge Universal MCP Server CLI'));
    console.log(chalk.gray('AI-powered content generation, SEO analysis, and chatbot\n'));
    
    console.log(chalk.cyan('📋 Quick Examples:'));
    console.log(chalk.white('━'.repeat(60)));
    console.log(chalk.yellow('# Check server status'));
    console.log('npx seo-forge status\n');
    
    console.log(chalk.yellow('# Generate content'));
    console.log('npx seo-forge generate -t "AI Technology" -k "AI,tech,innovation"\n');
    
    console.log(chalk.yellow('# Analyze SEO'));
    console.log('npx seo-forge analyze -c "Your content here" -k "seo,optimization"\n');
    
    console.log(chalk.yellow('# Chat with AI'));
    console.log('npx seo-forge chat -m "What is SEO?" -w "https://example.com"\n');
    
    console.log(chalk.yellow('# Generate image'));
    console.log('npx seo-forge image -p "professional business illustration"\n');
    
    console.log(chalk.yellow('# Create blog post'));
    console.log('npx seo-forge blog -t "Digital Marketing" -k "marketing,digital" --images 3\n');
    
    console.log(chalk.yellow('# Interactive mode'));
    console.log('npx seo-forge interactive\n');
    
    console.log(chalk.cyan('🌐 Server Information:'));
    console.log(chalk.white('━'.repeat(60)));
    console.log('Default Server: https://seoforge-mcp-server.onrender.com');
    console.log('GitHub: https://github.com/khiwniti/SEOForge-mcp-server');
    console.log('Documentation: Available in repository\n');
    
    console.log(chalk.cyan('💡 Tips:'));
    console.log('• Use --server to specify a different MCP server');
    console.log('• Use interactive mode for guided experience');
    console.log('• All commands support --help for detailed options');
  });

program.parse();

// Show help if no command provided
if (!process.argv.slice(2).length) {
  program.outputHelp();
}