import * as fs from 'fs';
import * as md5File from 'md5-file';
import * as path from 'path';
import { promisify } from 'util';

const copyFile = promisify(fs.copyFile);
const exists = promisify(fs.exists);
const stat = promisify(fs.stat);

if (process.argv.length !== 3) {
  throw new Error('Expected the path to the repository root as the only argument.');
}

const repository = process.argv[2];
if (!fs.existsSync(repository)) {
  throw new Error(`The path '${repository}' does not exist.`);
}
process.chdir(repository);

const getFileStatus = async (filename: string, directory: string): Promise<FileStatus> => {
  const file = path.join(directory, filename);
  if (await exists(file)) {
    const stats = await stat(file);

    return {
      checksum: md5File.sync(file),
      lastModified: stats.mtime,
    };
  }

  return null;
};

(async (): Promise<void> => {
  fs.readFile('syncTemplates.json', 'utf8', async (err: NodeJS.ErrnoException, content: string): Promise<void> => {
    if (!err) {
      const data: SyncTemplateConfiguration = JSON.parse(content);
      
      if ((<any>data).__proto__ === Object.prototype) {
        let filesCopied = 0;

        await Promise.all(data.templates.map(async (template: string): Promise<void> => {
          template = `${template}.tpl`;
          const status = await Promise.all([
            getFileStatus(template, data.directories[0]),
            getFileStatus(template, data.directories[1]),
          ]);

          if (status[0] === null && status[1] === null) {
            throw new Error(`Unknown file ${template}.`);
          }

          let copyTo = -1;
          if (status[0] === null) {
            copyTo = 0;
          } else if (status[1] === null) {
            copyTo = 1;
          } else if (status[0].checksum !== status[1].checksum) {
            copyTo = (status[0].lastModified > status[1].lastModified) ? 1 : 0;
          }

          if (copyTo !== -1) {
            const source = (copyTo === 0) ? 1 : 0;
            await copyFile(
              path.join(data.directories[source], template),
              path.join(data.directories[copyTo], template),
            );

            filesCopied++;
          }
        }));

        if (filesCopied === 0) {
          console.log("All files are in sync.");
        } else {
          console.log(`Copied ${filesCopied} of ${data.templates.length} files.`);
        }
      } else {
        throw new Error("Expected an object at the JSON root.");
      }
    }
  });
})();

interface SyncTemplateConfiguration {
  directories: string[];
  templates: string[];
}

interface FileStatus {
  checksum: string;
  lastModified: Date;
}
