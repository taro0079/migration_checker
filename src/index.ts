import * as fs from 'fs';
const readFileLines = (filePath: string):string[] => {
    return fs.readFileSync(filePath, 'utf-8').split('\n');
}

const getTableName = (filePath: string):string|undefined => {
    const matchPattern = /#\[ORM\\Table\(name:\s'(.*?)',/;
    const lines = readFileLines(filePath)

    const match = lines.find((line)=> matchPattern.exec(line))
    return match ? matchPattern.exec(match)![1] : undefined;
}

